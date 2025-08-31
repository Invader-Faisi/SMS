<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Repositories\TeacherManagementRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class TeacherManagementServices
{
    protected $teacherRepository;

    public function __construct(TeacherManagementRepository $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }

    public function getTeacherList($search,$perPage,$sortedBy,$sortDirection)
    {
        return $this->teacherRepository->getTeacherListData($search,$perPage,$sortedBy,$sortDirection);
    }

    public function getTeacherById($id)
    {
        return $this->teacherRepository->getTeacherByIdData($id);
    }

    public function saveTeacher($teacherData): bool|string
    {
        if(!empty($teacherData['image']))
        {
            $teacherImage = $teacherData['image'];
            $imagePath = $teacherImage->store('users/teachers', 'public');
            $teacherData['image'] = $imagePath;
        }

        $lastTeacher = $this->teacherRepository->getLastTeacherIdData();

        // Generating new teacher_id
        if ($lastTeacher && $lastTeacher->teacher_id) {
            $lastNumber = (int) str_replace('SMS-T-', '', $lastTeacher->teacher_id);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 0001;
        }

        $teacherData['teacher_id'] = 'SMS-T-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $teacher = new Teacher();
        $teacher->fill($teacherData);

        $response = $this->teacherRepository->saveTeacherData($teacher);

        if($response === true){
            $teacher['username'] = $teacherData['teacher_id'];
            return $this->addToUsers($teacher);
        }else{
            return $response;
        }
    }

    public function updateTeacher($teacherData,$teacherId)
    {
        if(!empty($teacherData['image'])){
            if ($teacherData['image'] instanceof UploadedFile) {
                // new file uploaded
                $teacherImage = $teacherData['image'];
                $imagePath = $teacherImage->store('users/teachers', 'public');
                $teacherData['image'] = $imagePath;
            }
        }

        return $this->teacherRepository->updateTeacherData($teacherData,$teacherId);
    }

    public function deleteTeacher($teacherId)
    {
        return $this->teacherRepository->deleteTeacherData($teacherId);
    }

    public function addToUsers($teacher): bool|string
    {
        $newUser = new User();
        $newUser['username'] = $teacher['username'];
        $newUser['role'] = 'Teacher';
        $newUser['password'] = Hash::make($teacher['password']);

        return $this->teacherRepository->addToUsersData($newUser);
    }

}
