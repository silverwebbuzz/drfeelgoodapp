<?php
/**
 * Patient Controller
 * Handles patient operations
 */

namespace App\Controllers;

use App\Models\Patient;
use App\Models\AdditionalInfo;
use App\Models\ProgressReport;

class PatientController {
    private $patientModel;
    private $additionalInfoModel;
    private $progressReportModel;

    public function __construct($db) {
        $this->patientModel = new Patient($db);
        $this->additionalInfoModel = new AdditionalInfo($db);
        $this->progressReportModel = new ProgressReport($db);
    }

    /**
     * Get all patients (for client-side DataTable)
     */
    public function getAllPatients() {
        try {
            $patients = $this->patientModel->getAll();

            return [
                'success' => true,
                'data' => $patients,
                'total' => count($patients)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching patient list: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get patient list with pagination
     */
    public function getList($page = 1, $limit = 10) {
        try {
            $patients = $this->patientModel->getPaginated($page, $limit);
            $total = $this->patientModel->getTotalCount();

            return [
                'success' => true,
                'data' => $patients,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching patient list: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get patient details with all related data
     */
    public function getDetail($patientId) {
        try {
            $patient = $this->patientModel->getById($patientId);

            if (!$patient) {
                return [
                    'success' => false,
                    'message' => 'Patient not found'
                ];
            }

            // Get additional info
            $additionalInfo = $this->additionalInfoModel->getByPatientId($patientId);

            // Get health summary
            $healthSummary = $this->additionalInfoModel->getHealthSummary($patientId);

            // Get family history
            $familyHistory = $this->additionalInfoModel->getFamilyHistory($patientId);

            // Get past medical history
            $medicalHistory = $this->additionalInfoModel->getPastMedicalHistory($patientId);

            // Get progress reports (last 20)
            $reports = $this->progressReportModel->getByPatientId($patientId, 20);
            $reportCount = $this->progressReportModel->getPatientReportCount($patientId);

            return [
                'success' => true,
                'patient' => $patient,
                'additional_info' => $additionalInfo,
                'health_summary' => $healthSummary,
                'family_history' => $familyHistory,
                'medical_history' => $medicalHistory,
                'progress_reports' => $reports,
                'total_reports' => $reportCount
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching patient details: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search patients
     */
    public function search($query) {
        if (strlen($query) < 2) {
            return [
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ];
        }

        try {
            $results = $this->patientModel->searchByName($query);

            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Search error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new patient
     */
    public function create($data) {
        try {
            $patientId = $this->patientModel->create($data);

            return [
                'success' => true,
                'message' => 'Patient created successfully',
                'patient_id' => $patientId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating patient: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update patient
     */
    public function update($patientId, $data) {
        try {
            $patient = $this->patientModel->getById($patientId);

            if (!$patient) {
                return [
                    'success' => false,
                    'message' => 'Patient not found'
                ];
            }

            // Whitelist editable fields
            $allowed = ['fname','lname','contact_no','dob','age','gender','mrg_status','veg',
                        'religion','education','occupation','refered_by','address','chief',
                        'dor','lname'];
            $data = array_intersect_key($data, array_flip($allowed));

            $this->patientModel->updatePatient($patientId, $data);

            return [
                'success' => true,
                'message' => 'Patient updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating patient: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add progress report for patient
     */
    public function addReport($patientId, $data) {
        try {
            $patient = $this->patientModel->getById($patientId);

            if (!$patient) {
                return [
                    'success' => false,
                    'message' => 'Patient not found'
                ];
            }

            $reportId = $this->progressReportModel->create($patientId, $data);

            return [
                'success' => true,
                'message' => 'Progress report added successfully',
                'report_id' => $reportId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error adding report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update a progress report
     */
    public function updateReport($reportId, $data) {
        try {
            $allowed = ['date', 'medicins', 'amt'];
            $clean = array_intersect_key($data, array_flip($allowed));

            if (empty($clean)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $this->progressReportModel->updateReport($reportId, $clean);

            return ['success' => true, 'message' => 'Report updated'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error updating report: ' . $e->getMessage()];
        }
    }

    /**
     * Get recent patients
     */
    public function getRecent($limit = 10) {
        try {
            $patients = $this->patientModel->getRecent($limit);

            return [
                'success' => true,
                'data' => $patients
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching recent patients: ' . $e->getMessage()
            ];
        }
    }
}