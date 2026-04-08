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
     * Get patient list with pagination
     */
    public function getPaginated($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT id, patient_id, fname, lname, contact_no, dob, gender, chief
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

        $this->update($id, $data);
        return true;
    }

    /**
     * Get recent patients (last N)
     */
    public function getRecent($limit = 10) {
        $limit = (int)$limit;

        $sql = "SELECT id, patient_id, fname, lname, contact_no, dob, gender, chief
                FROM {$this->table}
                ORDER BY dor DESC
                LIMIT {$limit}";

        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>