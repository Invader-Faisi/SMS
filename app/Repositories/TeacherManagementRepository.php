<?php

namespace App\Repositories;

use App\Models\Teacher;

class TeacherManagementRepository
{

    public function getTeacherListData($search, $perPage, $sortedBy, $sortDirection)
    {
        try {
            return Teacher::search($search)->orderBy($sortedBy,$sortDirection)->paginate($perPage);
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function getTeacherByIdData($id)
    {
        try{
            if (is_numeric($id)) {
                return Teacher::findOrFail($id);
            } else {
                return Teacher::where('teacher_id', $id)->firstOrFail();
            }
        }catch (\Exception $e){
            return null;
        }
    }

    public function getLastTeacherIdData()
    {
        try {
            return Teacher::orderByRaw("CAST(SUBSTRING(teacher_id, 7) AS UNSIGNED) DESC")->first();;
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveTeacherData(\App\Models\Teacher $teacher): bool|string
    {
        try {
            return $teacher->save();
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateTeacherData($teacherData, $teacherId)
    {
        try {
            return Teacher::where('teacher_id',$teacherId)->update($teacherData);
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateTeacherPassword($newPass, $teacherId)
    {
        try{
            return \App\Models\User::where('username', $teacherId)->update(['password' => $newPass]);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function deleteTeacherData($teacherId)
    {
        try {
            return Teacher::where('teacher_id',$teacherId)->delete();
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function addToUsersData(\App\Models\User $newUser): bool|string
    {
        try {
            return $newUser->save();
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function getTeacherListForTimeTable(): \Illuminate\Database\Eloquent\Collection|string
    {
        try {
            return Teacher::all();
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }

    public function getTeachersBySubjectData($subject)
    {
        try {
            return Teacher::where('designation', 'Teacher-'.$subject)->get();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }
}
