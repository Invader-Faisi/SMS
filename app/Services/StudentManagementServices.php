<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Repositories\StudentManagementRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
class StudentManagementServices
{
    protected $studentRepository;

    public function __construct(StudentManagementRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function getStudentList($search,$perPage,$sortedBy,$sortDirection)
    {
        return $this->studentRepository->getStudentListData($search,$perPage,$sortedBy,$sortDirection);
    }

    public function getStudentById($id)
    {
        return $this->studentRepository->getStudentByIdData($id);
    }

    public function saveStudent($studentData)
    {
        if(!empty($studentData['image']))
        {
            $studentImage = $studentData['image'];
            $imagePath = $studentImage->store('users/students', 'public');
            $studentData['image'] = $imagePath;
        }

        $lastStudent = $this->studentRepository->getLastStudentIdData();

        // Generating new student_id
        if ($lastStudent && $lastStudent->student_id) {
            $lastNumber = (int) str_replace('SMS-'.$studentData['class'].'-'.$studentData['section'].'-', '', $lastStudent->student_id);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 0001;
        }

        $studentData['student_id'] = 'SMS-'.$studentData['class'].'-'.$studentData['section'].'-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $student = new Student();
        $student->fill($studentData);

        $response = $this->studentRepository->saveStudentData($student);

        if($response === true){
            $student['username'] = $studentData['student_id'];
            return $this->addToUsers($student);
        }else{
            return $response;
        }
    }

    public function updateStudent($studentData,$studentId)
    {
        if(!empty($studentData['image'])){
            if ($studentData['image'] instanceof UploadedFile) {
                // new file uploaded
                $studentId = $studentData['image'];
                $imagePath = $studentId->store('users/students', 'public');
                $studentData['image'] = $imagePath;
            }
        }

        return $this->studentRepository->updateStudentData($studentData,$studentId);
    }

    public function getParent($studentId)
    {
        return $this->studentRepository->getParentData($studentId);
    }

    public function deleteStudent($studentId)
    {
        return $this->studentRepository->deleteStudentData($studentId);
    }

    public function addToUsers($student): bool|string
    {
        $parent = $this->getParent($student['parent_id']);
        $newUser = new User();
        $newUser['username'] = $student['username'];
        $newUser['name'] = $student['name'];
        $newUser['email'] = $parent->email;
        $newUser['password'] = Hash::make($student['password']);
        $newUser['mobile'] = $parent->mobile;
        $newUser['address'] = $parent->address;

        return $this->studentRepository->addToUsersData($newUser);
    }




}
