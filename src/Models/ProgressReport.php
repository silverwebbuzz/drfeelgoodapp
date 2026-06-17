<?php
/**
 * Progress Report Model
 * Handles patient treatment history
 */

namespace App\Models;

use PDO;

class ProgressReport extends BaseModel {
    protected $table = 'progress_report';

    /**
     * Get all progress reports for a patient
     */
    public function getByPatientId($patientId, $limit = 50, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT id, p_id, date, medicins, notes, reports_notes, amt, payment_type, payment_status
                FROM {$this->table}
                WHERE p_id = ?
                ORDER BY date DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->query($sql, [$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get count of reports for a patient
     */
    public function getPatientReportCount($patientId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE p_id = ?";
        $stmt = $this->query($sql, [$patientId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Add progress report
     */
    public function create($patientId, $data) {
        if (empty($patientId)) {
            throw new \Exception("Patient ID is required");
        }
        if (empty($data['medicins'] ?? null) && empty($data['notes'] ?? null) && empty($data['reports_notes'] ?? null)) {
            throw new \Exception("Medicines or notes are required");
        }

        $reportData = [
            'p_id'           => $patientId,
            'date'           => $data['date'] ?? date('Y-m-d H:i:s'),
            'medicins'       => $data['medicins'] ?? '',
            'notes'          => $data['notes']    ?? '',
            'reports_notes'  => $data['reports_notes'] ?? '',
            'amt'            => $data['amt']      ?? 0,
            'payment_type'   => in_array($data['payment_type'] ?? '', ['cash', 'online']) ? $data['payment_type'] : 'cash',
            'payment_status' => in_array($data['payment_status'] ?? '', ['paid', 'remaining']) ? $data['payment_status'] : 'paid',
        ];

        return $this->insert($reportData);
    }

    /**
     * Update progress report
     */
    public function updateReport($id, $data) {
        unset($data['id'], $data['p_id']);
        $this->update($id, $data);
        return true;
    }

    /**
     * Get recent reports across all patients
     */
    public function getRecent($limit = 20) {
        $limit = (int)$limit;

        $sql = "SELECT
                    pr.id, pr.p_id, pr.date, pr.medicins, pr.notes, pr.reports_notes, pr.amt,
                    pr.payment_type, pr.payment_status,
                    CONCAT(p.fname, ' ', p.lname) as patient_name
                FROM {$this->table} pr
                JOIN patient p ON pr.p_id = p.id
                ORDER BY pr.date DESC
                LIMIT {$limit}";

        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get reports by date range
     */
    public function getByDateRange($startDate, $endDate, $limit = null) {
        $sql = "SELECT
                    pr.id, pr.p_id, pr.date, pr.medicins, pr.notes, pr.reports_notes, pr.amt,
                    pr.payment_type, pr.payment_status,
                    CONCAT(p.fname, ' ', p.lname) as patient_name
                FROM {$this->table} pr
                JOIN patient p ON pr.p_id = p.id
                WHERE pr.date BETWEEN ? AND ?
                ORDER BY pr.date DESC";

        if ($limit) {
            $limit = (int)$limit;
            $sql .= " LIMIT {$limit}";
            $stmt = $this->query($sql, [$startDate, $endDate]);
        } else {
            $stmt = $this->query($sql, [$startDate, $endDate]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>