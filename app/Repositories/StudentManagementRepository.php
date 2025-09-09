<?php

namespace App\Repositories;

use Log;
use Carbon\Carbon;
use App\Models\Parents;
use App\Models\Student;
use App\Models\Attendance;

class StudentManagementRepository
{
    public function __construct()
    {
//
    }

    public function getStudentListData($search, $perPage, $sortedBy, $sortDirection)
    {
        try{
            return Student::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getStudentByIdData($id)
    {
        try{
            if (is_numeric($id)) {
                return Student::findOrFail($id);
            } else {
                return Student::where('student_id', $id)->firstOrFail();
            }
        }catch (\Exception $e){
            return null;
        }
    }

    public function getLastStudentIdData()
    {
        try{
            return Student::Latest('id')->first();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveStudentData($student)
    {
        try{
            return $student->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateStudentData($studentData,$studentId)
    {
        try{
            return Student::where('student_id', $studentId)->update($studentData);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateStudentPassword($newPass, $studentId)
    {
        try{
            return \App\Models\User::where('username', $studentId)->update(['password' => $newPass]);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateStudentUsername(string $newStudentId,$previousId)
    {
        try{
            return \App\Models\User::where('username', $previousId)->update(['username' => $newStudentId]);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getParentData($parentId)
    {
        try{
            return Parents::where('parent_id', $parentId)->first();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function deleteStudentData($studentId)
    {
        try{
            return Student::where('student_id', $studentId)->delete();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function addToUsersData(\App\Models\User $newUser)
    {
        try{
            return $newUser->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getStudentsByClassData(string $id, $search): \Illuminate\Database\Eloquent\Collection|string
    {
        try{

            $parts = explode('-', $id);
            $class = $parts[0] ?? null;
            $section = $parts[1] ?? null;

            $query = Student::query();

            if ($class) {
                $query->where('class', $class);
            }

            if ($section) {
                $query->where('section', $section);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            return $query->get();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function addStudentsAttendanceData(array $attendance)
{
    try {
        $now = now()->toDateTimeString();
        foreach ($attendance as &$row) {
            if (!isset($row['created_at'])) {
                $row['created_at'] = $now;
            }
            $row['updated_at'] = $now;
        }

        Attendance::upsert(
            $attendance,
            ['student_id', 'date'], // Unique keys
            ['class_id', 'status', 'remarks', 'updated_at'] // Columns to update
        );

        return true;
    } catch (\Exception $e) {
        return 'Error : ' . $e->getMessage();
    }
}


    public function getStudentAttendanceData($studentId, $date)
    {
        try{
            return Attendance::with('student')->where('student_id', $studentId)->where('date', $date)->first();    
        }catch (\Exception $e){
                return null; 
        }
    }

    public function getClassAttendanceData($date)
    {
        try{
            $students = Student::with(['attendances' => function($query) use ($date) {
                $query->where('date', $date);
            }])->get()->groupBy(function ($student) {
                return $student->class . '-' . $student->section;
            }); 
            return $students;  
        }catch (\Exception $e){
                return 'Error : '.$e->getMessage();
        }
    }

    public function getAttendanceForClassByMonthData($classId, $month)
    {
        try {
            [$className, $section] = explode('-', $classId);

            // Get first and last date of the month
            $start = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
            $end = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

            $students = Student::with(['attendances' => function($query) use ($start, $end) {
                $query->whereBetween('date', [$start, $end]);
            }])
            ->where('class', $className)
            ->where('section', $section)
            ->get();

            return $students;

        } catch (\Exception $e) {
            return collect(); // return empty collection if error
        }
    }

}
