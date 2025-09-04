<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\Result;
use App\Services\EczAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResultsReportExport;

class AnalysisController extends Controller
{
    public function index()
    {
        return view('analysis');
    }

    public function analyze(Request $request, EczAnalysisService $analysisService)
    {
        $request->validate([
            'results_csv' => 'required|file|mimes:csv,txt',
            'grade_level' => 'required|in:9,12',
        ]);

        Schema::disableForeignKeyConstraints();
        Result::truncate();
        Pupil::truncate();
        Schema::enableForeignKeyConstraints();

        // --- NEW LOGIC START ---

        // Step A: Read the entire CSV and pre-process it into a structured array
        $allScores = [];
        $pupilData = [];

        $file = fopen($request->file('results_csv'), 'r');
        $header = fgetcsv($file);
        
        $pupilIdCol = array_search('Pupil ID', $header);
        $pupilNameCol = array_search('Pupil', $header);
        $pupilClassCol = array_search("Pupil's Class", $header);
        $subjectCol = array_search('Subject', $header);
        $scoreCol = array_search('Score', $header);
        $assessmentCol = array_search('Assessment', $header);
        $genderCol = array_search('Gender', $header);

        while (($line = fgetcsv($file)) !== FALSE) {
            $pupilId = $line[$pupilIdCol];
            $subject = trim($line[$subjectCol]);
            $assessment = trim($line[$assessmentCol]);
            $score = ($line[$scoreCol] == -1) ? null : (int)$line[$scoreCol];

            // Store all pupil details once
            if (!isset($pupilData[$pupilId])) {
                $pupilData[$pupilId] = [
                    'pupil_id' => $pupilId,
                    'pupil_name' => $line[$pupilNameCol],
                    'gender' => ($genderCol !== false && in_array(strtoupper($line[$genderCol]), ['B', 'G'])) ? strtoupper($line[$genderCol]) : null,
                    'pupil_class' => $line[$pupilClassCol]
                ];
            }

            // Store scores grouped by pupil, subject, and assessment type
            if ($assessment === 'MID-TERM' || $assessment === 'END OF TERM') {
                $allScores[$pupilId][$subject][$assessment] = $score;
            }
        }
        fclose($file);

        // Step B: Combine scores according to our new rules
        $finalResults = [];
        $now = now();
        foreach ($allScores as $pupilId => $subjects) {
            foreach ($subjects as $subjectName => $assessments) {
                $midTermScore = $assessments['MID-TERM'] ?? null;
                $endOfTermScore = $assessments['END OF TERM'] ?? null;
                $finalScore = null;

                // Rule #1: If both exist, add them.
                if (isset($midTermScore) && isset($endOfTermScore)) {
                    $finalScore = $midTermScore + $endOfTermScore;
                } 
                // Rule #2: If only end-of-term exists, use it.
                elseif (isset($endOfTermScore)) {
                    $finalScore = $endOfTermScore;
                }
                // Rule #3 (Mid-term only) is implicitly handled by doing nothing.

                // Only add a result if we have a final score to analyze
                if (isset($finalScore)) {
                    $finalResults[] = [
                        'pupil_id_temp' => $pupilId,
                        'subject' => $subjectName,
                        'score' => $finalScore,
                        'assessment' => 'END OF TERM', // Standardize for the service
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Step C: Mass insert the processed data into the database
        if (!empty($pupilData)) {
            Pupil::insert(array_values($pupilData));
            $pupilIdMap = Pupil::pluck('pupil_db_id', 'pupil_id');
            
            foreach ($finalResults as &$result) {
                if (isset($pupilIdMap[$result['pupil_id_temp']])) {
                    $result['pupil_db_id'] = $pupilIdMap[$result['pupil_id_temp']];
                }
                unset($result['pupil_id_temp']);
            }
            
            if(!empty($finalResults)){
                foreach (array_chunk($finalResults, 500) as $chunk) {
                    Result::insert($chunk);
                }
            }
        }
        
        // --- NEW LOGIC END ---

        $analysisResults = $analysisService->generateReport((int)$request->grade_level);

        session(['analysis_results' => $analysisResults, 'grade_level' => (int)$request->grade_level]);

        return redirect()->route('analysis.index');
    }

    public function exportReport()
    {
        $results = session('analysis_results');
        $gradeLevel = session('grade_level');
        if (!$results || !$gradeLevel) {
            return redirect()->route('analysis.index');
        }
        return Excel::download(new ResultsReportExport($results, $gradeLevel), 'Results_Analysis_Report.xlsx');
    }
    
    public function clearSession()
    {
        session()->forget(['analysis_results', 'grade_level']);
        return redirect()->route('analysis.index');
    }
}