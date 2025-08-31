<?php

namespace App\Repositories;

use App\Models\Parents;
use App\Models\Student;

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

}
