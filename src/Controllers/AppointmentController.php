<?php
namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Patient;

class AppointmentController {
    private $apptModel;
    private $settingModel;
    private $patientModel;

    public function __construct($db) {
        $this->apptModel    = new Appointment($db);
        $this->settingModel = new Setting($db);
        $this->patientModel = new Patient($db);
    }

    /** Today's queue page data */
    public function getQueue($date = null) {
        $date  = $date ?? date('Y-m-d');
        $queue = $this->apptModel->getByDate($date);
        $stats = $this->apptModel->todayStats($date);
        return ['success' => true, 'queue' => $queue, 'stats' => $stats, 'date' => $date];
    }

    /** Create walk-in token (receptionist) */
    public function createWalkin($data, $userId = null) {
        try {
            // If patient_id given, verify exists
            if (!empty($data['patient_id'])) {
                $patient = $this->patientModel->getById($data['patient_id']);
                if (!$patient) return ['success' => false, 'message' => 'Patient not found'];
                $data['patient_name']  = trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));
                $data['patient_phone'] = $patient['contact_no'] ?? '';
                $data['is_new_patient'] = 0;
            } else {
                $data['is_new_patient'] = 1;
            }

            $id    = $this->apptModel->createWalkin($data, $userId);
            $appt  = $this->apptModel->getById($id);
            return ['success' => true, 'message' => 'Token created', 'token' => $appt['token_number'], 'id' => $id];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Update appointment status (AJAX) */
    public function updateStatus($id, $status) {
        $allowed = ['waiting', 'in_consultation', 'completed', 'cancelled', 'no_show'];
        if (!in_array($status, $allowed)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        try {
            $this->apptModel->updateStatus($id, $status);
            $appt = $this->apptModel->getByIdFull($id);

            $redirect = null;
            // Call → go to patient detail page
            if ($status === 'in_consultation' && !empty($appt['patient_id'])) {
                $redirect = '/patient/' . (int)$appt['patient_id'];
            }
            // Completed → go back to queue
            if ($status === 'completed') {
                $redirect = '/queue';
            }

            return ['success' => true, 'status' => $status, 'redirect' => $redirect];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Get available slots for public booking page */
    public function getAvailableSlots($date) {
        try {
            // Check if clinic is closed that day
            $closedSql = "SELECT id FROM clinic_closed_dates WHERE date = ?";

            $allSlots    = $this->settingModel->generateSlots($date);
            $bookedSlots = $this->apptModel->bookedSlots($date);
            $maxPerSlot  = (int)$this->settingModel->get('max_per_slot', 1);

            // Count bookings per slot
            $bookedCount = array_count_values($bookedSlots);

            $result = [];
            foreach ($allSlots as $slot) {
                $booked = $bookedCount[$slot] ?? 0;
                $result[] = [
                    'time'      => $slot,
                    'available' => $booked < $maxPerSlot,
                    'booked'    => $booked,
                ];
            }
            return ['success' => true, 'slots' => $result, 'date' => $date];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Public booking — lookup patient by phone */
    public function lookupByPhone($phone) {
        try {
            $patient = $this->patientModel->findByPhone($phone);
            if ($patient) {
                return ['success' => true, 'found' => true, 'patient' => $patient];
            }
            return ['success' => true, 'found' => false];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Create pre-booked appointment (public page) */
    public function createPrebooked($data) {
        try {
            // Validate slot still available
            $slots = $this->getAvailableSlots($data['appt_date']);
            $slotAvailable = false;
            foreach ($slots['slots'] ?? [] as $s) {
                if ($s['time'] === $data['slot_time'] && $s['available']) {
                    $slotAvailable = true; break;
                }
            }
            if (!$slotAvailable) {
                return ['success' => false, 'message' => 'This slot is no longer available'];
            }

            // Link existing patient
            if (!empty($data['patient_id'])) {
                $patient = $this->patientModel->getById($data['patient_id']);
                if ($patient) {
                    $data['patient_name']  = trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));
                    $data['patient_phone'] = $patient['contact_no'] ?? '';
                    $data['is_new_patient'] = 0;
                }
            } else {
                $data['is_new_patient'] = 1;
            }

            $id   = $this->apptModel->createPrebooked($data);
            $appt = $this->apptModel->getById($id);
            return [
                'success'      => true,
                'message'      => 'Appointment booked successfully',
                'token'        => $appt['token_number'],
                'id'           => $id,
                'slot_time'    => $data['slot_time'],
                'appt_date'    => $data['appt_date'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Settings page save */
    public function saveSettings($data) {
        try {
            $allowed = [
                'slot_duration_min',
                'mon_sat_morning_on','mon_sat_morning_start','mon_sat_morning_end',
                'mon_sat_evening_on','mon_sat_evening_start','mon_sat_evening_end',
                'sunday_on','sunday_start','sunday_end',
                'max_per_slot','clinic_name','clinic_phone','consultation_fee',
            ];
            $clean = array_intersect_key($data, array_flip($allowed));
            // Checkboxes not sent when off — set to 0
            foreach (['mon_sat_morning_on','mon_sat_evening_on','sunday_on'] as $cb) {
                if (!isset($clean[$cb])) $clean[$cb] = '0';
            }
            $this->settingModel->setMany($clean);
            return ['success' => true, 'message' => 'Settings saved'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
