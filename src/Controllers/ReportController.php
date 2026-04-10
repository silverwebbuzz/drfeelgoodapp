<?php
namespace App\Controllers;

use App\Models\Report;

class ReportController {
    private $model;

    public function __construct($db) {
        $this->model = new Report($db);
    }

    /** Parse & validate period / custom dates from request params */
    private function resolveDates(array $params): array {
        $period = $params['period'] ?? 'week';
        if ($period === 'custom') {
            $from = $params['from'] ?? date('Y-m-d');
            $to   = $params['to']   ?? date('Y-m-d');
            // Ensure from <= to
            if ($from > $to) [$from, $to] = [$to, $from];
        } else {
            [$from, $to] = Report::periodDates($period);
        }
        return [$period, $from, $to];
    }

    // ── Income ────────────────────────────────────────────────────────────────

    public function income(array $params = []): array {
        [$period, $from, $to] = $this->resolveDates($params);
        $year = (int)($params['year'] ?? date('Y'));

        $summary    = $this->model->incomeSummary($from, $to);
        $byDay      = $this->model->incomeByDay($from, $to);
        $byMonth    = $this->model->incomeByMonth($year);
        $byWeek     = $this->model->incomeByWeek($from, $to);

        return compact('period', 'from', 'to', 'year', 'summary', 'byDay', 'byMonth', 'byWeek');
    }

    // ── Patient Analytics ─────────────────────────────────────────────────────

    public function patients(array $params = []): array {
        [$period, $from, $to] = $this->resolveDates($params);
        $year = (int)($params['year'] ?? date('Y'));

        $byDay         = $this->model->newPatientsByDay($from, $to);
        $byMonth       = $this->model->newPatientsByMonth($year);
        $gender        = $this->model->genderSplit();
        $ageGroups     = $this->model->ageGroups();
        $complaints    = $this->model->topComplaints(10);
        $newReturning  = $this->model->newVsReturning($from, $to);

        return compact('period', 'from', 'to', 'year', 'byDay', 'byMonth', 'gender', 'ageGroups', 'complaints', 'newReturning');
    }

    // ── Queue / Operations ────────────────────────────────────────────────────

    public function queueOps(array $params = []): array {
        [$period, $from, $to] = $this->resolveDates($params);

        $byDay       = $this->model->appointmentsByDay($from, $to);
        $consultTime = $this->model->avgConsultTime($from, $to);
        $busyDays    = $this->model->busyDays($from, $to);
        $busySlots   = $this->model->busySlots($from, $to);
        $noShow      = $this->model->noShowRate($from, $to);

        return compact('period', 'from', 'to', 'byDay', 'consultTime', 'busyDays', 'busySlots', 'noShow');
    }

    // ── Medicines ─────────────────────────────────────────────────────────────

    public function medicines(array $params = []): array {
        [$period, $from, $to] = $this->resolveDates($params);

        $topMeds    = $this->model->topMedicines($from, $to, 15);
        $byDay      = $this->model->prescriptionsByDay($from, $to);

        return compact('period', 'from', 'to', 'topMeds', 'byDay');
    }

    // ── Doctor Productivity ───────────────────────────────────────────────────

    public function productivity(array $params = []): array {
        [$period, $from, $to] = $this->resolveDates($params);

        $summary      = $this->model->productivitySummary($from, $to);
        $byDay        = $this->model->patientsSeen($from, $to);
        $consultTrend = $this->model->consultTimeTrend($from, $to);
        $busyDays     = $this->model->busyDays($from, $to);

        return compact('period', 'from', 'to', 'summary', 'byDay', 'consultTrend', 'busyDays');
    }
}
