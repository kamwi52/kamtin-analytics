<?php

namespace App\Services;

use App\Models\Pupil;
use Illuminate\Database\Eloquent\Collection;

class EczAnalysisService
{
    public function generateReport(int $gradeLevel): array
    {
        $allPupils = Pupil::with('results')->get();

        $malePupils = $allPupils->where('gender', 'B');
        $femalePupils = $allPupils->where('gender', 'G');
        $unknownGenderPupils = $allPupils->whereNotIn('gender', ['B', 'G']);

        $maleCounts = $this->_calculateQualificationCounts($malePupils, $gradeLevel);
        $femaleCounts = $this->_calculateQualificationCounts($femalePupils, $gradeLevel);
        $unknownCounts = $this->_calculateQualificationCounts($unknownGenderPupils, $gradeLevel);

        // Combine totals, ensuring unknown genders are added to the main counts
        $totalRegistered = $allPupils->count();
        $totalSat = $maleCounts['sat'] + $femaleCounts['sat'] + $unknownCounts['sat'];
        $totalAbsent = $totalRegistered - $totalSat;
        $totalCertificate = $maleCounts['certificate'] + $femaleCounts['certificate'] + $unknownCounts['certificate'];
        $totalStatement = $maleCounts['statement'] + $femaleCounts['statement'] + $unknownCounts['statement'];
        $totalFail = $maleCounts['fail'] + $femaleCounts['fail'] + $unknownCounts['fail'];
        $totalQuantitativePass = $totalCertificate + $totalStatement;
        
        // Calculate Qualitative Pass (percentage)
        $qualitativePassTotal = ($totalSat > 0) ? round(($totalQuantitativePass / $totalSat) * 100, 2) : 0;
        $qualitativePassBoys = ($maleCounts['sat'] > 0) ? round((($maleCounts['certificate'] + $maleCounts['statement']) / $maleCounts['sat']) * 100, 2) : 0;
        $qualitativePassGirls = ($femaleCounts['sat'] > 0) ? round((($femaleCounts['certificate'] + $femaleCounts['statement']) / $femaleCounts['sat']) * 100, 2) : 0;

        return [
            'TOTAL REGISTERED' => ['B' => $malePupils->count(), 'G' => $femalePupils->count(), 'TOTAL' => $totalRegistered],
            'TOTAL SAT' => ['B' => $maleCounts['sat'], 'G' => $femaleCounts['sat'], 'TOTAL' => $totalSat],
            'TOTAL ABSENT' => ['B' => $malePupils->count() - $maleCounts['sat'], 'G' => $femalePupils->count() - $femaleCounts['sat'], 'TOTAL' => $totalAbsent],
            'CERTIFICATE' => ['B' => $maleCounts['certificate'], 'G' => $femaleCounts['certificate'], 'TOTAL' => $totalCertificate],
            'STATEMENT' => ['B' => $maleCounts['statement'], 'G' => $femaleCounts['statement'], 'TOTAL' => $totalStatement],
            'FAIL' => ['B' => $maleCounts['fail'], 'G' => $femaleCounts['fail'], 'TOTAL' => $totalFail],
            'QUANTITATIVE PASS' => ['B' => $maleCounts['certificate'] + $maleCounts['statement'], 'G' => $femaleCounts['certificate'] + $femaleCounts['statement'], 'TOTAL' => $totalQuantitativePass],
            'QUALITATIVE PASS (%)' => ['B' => $qualitativePassBoys, 'G' => $qualitativePassGirls, 'TOTAL' => $qualitativePassTotal],
        ];
    }

    private function _calculateQualificationCounts(Collection $pupils, int $gradeLevel): array
    {
        $counts = ['sat' => 0, 'certificate' => 0, 'statement' => 0, 'fail' => 0];
        foreach ($pupils as $pupil) {
            if ($pupil->results->isEmpty()) continue;
            
            $counts['sat']++;
            $qualification = ($gradeLevel == 9) ? $this->analyzeGrade9($pupil) : $this->analyzeGrade12($pupil);
            if ($qualification === 'Certificate') $counts['certificate']++;
            elseif ($qualification === 'Statement') $counts['statement']++;
            else $counts['fail']++;
        }
        return $counts;
    }

    /**
     * FULLY IMPLEMENTED METHOD
     */
    private function analyzeGrade9(Pupil $pupil): string
    {
        if ($pupil->results->isEmpty()) return 'Fail';
        $passCount = 0;
        foreach ($pupil->results as $result) {
            if ($result->score >= 40) $passCount++;
        }
        if ($passCount >= 6) return 'Certificate';
        if ($passCount >= 1) return 'Statement';
        return 'Fail';
    }

    /**
     * FULLY IMPLEMENTED METHOD
     */
    private function analyzeGrade12(Pupil $pupil): string
    {
        if ($pupil->results->isEmpty()) return 'Fail';
        $passCount = 0;
        $creditCount = 0;
        $englishPassed = false;
        foreach ($pupil->results as $result) {
            if ($result->score >= 40) {
                $passCount++;
                if (strtolower(trim($result->subject)) === 'english') $englishPassed = true;
            }
            if ($result->score >= 50) $creditCount++;
        }
        $ruleA = ($englishPassed && $passCount >= 6 && $creditCount >= 1);
        $ruleB = ($englishPassed && $passCount >= 5 && $creditCount >= 2);
        if ($ruleA || $ruleB) return 'Certificate';
        if ($passCount >= 1) return 'Statement';
        return 'Fail';
    }
}