<?php
/**
 * Patient Model
 * Handles patient data operations
 */

namespace App\Models;

use PDO;

class Patient extends BaseModel {
    protected $table = 'patient';

    /**
     * Get patient by ID with all related information
     */
    public function getWithDetails($id) {
        $sql = "SELECT
                    p.*,
                    ai.*
                FROM {$this->table} p
                LEFT JOIN additional_info ai ON p.id = ai.p_id
                WHERE p.id = ?";

        $stmt = $this->query($sql, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get patient with progress reports
     */
    public function getWithReports($id, $limit = 50) {
        $limit = (int)$limit;

        $sql = "SELECT
                    p.*,
                    pr.id as report_id,
                    pr.date as report_date,
                    pr.medicins,
                    pr.amt
                FROM {$this->table} p
                LEFT JOIN progress_report pr ON p.id = pr.p_id
                WHERE p.id = ?
                ORDER BY pr.date DESC
                LIMIT {$limit}";

        $stmt = $this->query($sql, [$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search patients by name
     */
    public function findByPhone($phone) {
        $sql = "SELECT id, patient_id, fname, lname, contact_no
                FROM {$this->table} WHERE contact_no = ? LIMIT 1";
        $stmt = $this->query($sql, [$phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function searchByName($name) {
        $sql = "SELECT id, fname, lname, contact_no, dob, gender, chief
                FROM {$this->table}
                WHERE CONCAT(fname, ' ', lname) LIKE ?
                OR fname LIKE ?
                OR lname LIKE ?
                OR contact_no LIKE ?
                ORDER BY fname, lname
                LIMIT 20";

        $searchTerm = "%{$name}%";
        $stmt = $this->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all patients
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT id, patient_id, fname, lname, contact_no, dob, age, gender, mrg_status, chief, dor
                FROM {$this->table}
                ORDER BY fname, lname";

        if ($limit) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get patient list with pagination
     */
    public function getPaginated($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT id, patient_id, fname, lname, contact_no, dob, age, gender, mrg_status, chief, dor
                FROM {$this->table}
                ORDER BY fname, lname
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total patient count
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Add new patient
     */
    /**
     * Quick-create a minimal patient record from appointment data (name + phone only)
     * Used when walk-in or booking patient is not in the system yet
     */
    public function createQuick($name, $phone, $chief = '') {
        $parts = explode(' ', trim($name), 2);
        $fname = $parts[0] ?? $name;
        $lname = $parts[1] ?? '';
        return $this->insert([
            'patient_id'  => 'AUTO-' . date('ymd') . '-' . strtoupper(substr($fname, 0, 3)),
            'fname'       => $fname,
            'lname'       => $lname,
            'contact_no'  => $phone,
            'chief'       => $chief,
            'dor'         => date('Y-m-d'),
            'is_new_patient' => 1,
        ]);
    }

    public function create($data) {
        $requiredFields = ['fname', 'lname', 'contact_no', 'dob'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field] ?? null)) {
                throw new \Exception("Field '{$field}' is required");
            }
        }

        // Set default values
        $data['dor'] = $data['dor'] ?? date('Y-m-d');
        $data['patient_id'] = $data['patient_id'] ?? time(); // Fallback to timestamp

        return $this->insert($data);
    }

    /**
     * Update patient info
     */
    public function updatePatient($id, $data) {
        // Don't allow updating critical fields
        unset($data['id'], $data['patient_id']);

        // Convert empty date fields to NULL so MySQL doesn't get ''
        $dateFields = ['dob', 'dor'];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $data) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }

        // Convert empty numeric fields to NULL
        $numericFields = ['age'];
        foreach ($numericFields as $field) {
            if (array_key_exists($field, $data) && trim((string)$data[$field]) === '') {
                $data[$field] = null;
            }
        }

        $this->update($id, $data);
        return true;
    }

    /**
     * Get recent patients (last N)
     */
    public function getRecent($limit = 10) {
        $limit = (int)$limit;

        $sql = "SELECT id, patient_id, fname, lname, contact_no, dob, age, gender, mrg_status, chief, dor
                FROM {$this->table}
                ORDER BY dor DESC
                LIMIT {$limit}";

        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>