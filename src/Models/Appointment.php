<?php
namespace App\Models;
use PDO;

class Appointment extends BaseModel {
    protected $table = 'appointments';

    /** Today's queue — all appointments for a date */
    public function getByDate($date) {
        $sql = "SELECT a.*, p.fname, p.lname, p.contact_no
                FROM {$this->table} a
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE a.appt_date = ?
                ORDER BY a.token_number ASC, a.slot_time ASC";
        $stmt = $this->query($sql, [$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Next available token number for a date */
    public function nextToken($date) {
        $sql = "SELECT MAX(token_number) as max_token FROM {$this->table} WHERE appt_date = ?";
        $stmt = $this->query($sql, [$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_token'] ?? 0) + 1;
    }

    /** How many bookings exist for a specific date+time slot */
    public function countSlot($date, $time) {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}
                WHERE appt_date = ? AND slot_time = ?
                AND status NOT IN ('cancelled','no_show')";
        $stmt = $this->query($sql, [$date, $time]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['cnt'];
    }

    /** Booked slot times for a date (for public booking page) */
    public function bookedSlots($date) {
        $sql = "SELECT slot_time FROM {$this->table}
                WHERE appt_date = ? AND status NOT IN ('cancelled','no_show')
                AND slot_time IS NOT NULL";
        $stmt = $this->query($sql, [$date]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'slot_time');
    }

    /** Create walk-in token */
    public function createWalkin($data, $userId = null) {
        $date = $data['appt_date'] ?? date('Y-m-d');
        $insert = [
            'patient_id'      => $data['patient_id'] ?? null,
            'appt_date'       => $date,
            'slot_time'       => null,
            'token_number'    => $this->nextToken($date),
            'type'            => 'walkin',
            'status'          => 'waiting',
            'patient_name'    => $data['patient_name'] ?? null,
            'patient_phone'   => $data['patient_phone'] ?? null,
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'is_new_patient'  => $data['is_new_patient'] ?? 0,
            'is_followup'     => $data['is_followup'] ?? 0,
            'created_by'      => $userId,
        ];
        return $this->insert($insert);
    }

    /** Create pre-booked appointment (public page) */
    public function createPrebooked($data) {
        $date = $data['appt_date'];
        $insert = [
            'patient_id'      => $data['patient_id'] ?? null,
            'appt_date'       => $date,
            'slot_time'       => $data['slot_time'],
            'token_number'    => $this->nextToken($date),
            'type'            => 'prebooked',
            'status'          => 'waiting',
            'patient_name'    => $data['patient_name'] ?? null,
            'patient_phone'   => $data['patient_phone'] ?? null,
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'is_new_patient'  => $data['is_new_patient'] ?? 0,
            'is_followup'     => $data['is_followup'] ?? 0,
            'created_by'      => null,
        ];
        return $this->insert($insert);
    }

    /** Update appointment status */
    public function updateStatus($id, $status) {
        $now = date('Y-m-d H:i:s'); // IST — set by date_default_timezone_set in index.php
        $extra = [];
        if ($status === 'in_consultation') $extra['called_at']    = $now; // Dr attended patient
        if ($status === 'completed')       $extra['completed_at'] = $now; // Patient out
        $data = array_merge(['status' => $status], $extra);
        $this->update($id, $data);
    }

    /** Get single appointment with patient join */
    public function getByIdFull($id) {
        $sql = "SELECT a.*, p.fname, p.lname, p.contact_no
                FROM {$this->table} a
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE a.id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Stats for today */
    public function todayStats($date) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(status='waiting') as waiting,
                    SUM(status='in_consultation') as in_consultation,
                    SUM(status='completed') as completed,
                    SUM(type='walkin') as walkins,
                    SUM(type='prebooked') as prebooked
                FROM {$this->table} WHERE appt_date = ?";
        $stmt = $this->query($sql, [$date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Recent appointments list for history */
    public function getRecent($limit = 50) {
        $limit = (int)$limit;
        $sql = "SELECT a.*, p.fname, p.lname, p.contact_no
                FROM {$this->table} a
                LEFT JOIN patient p ON a.patient_id = p.id
                ORDER BY a.appt_date DESC, a.token_number ASC
                LIMIT {$limit}";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
