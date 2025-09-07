<?php

namespace App\Services;

use App\Models\Classes;
use App\Repositories\ClassManagementRepository;

class ClassManagementServices
{
    protected $classRepository;
    public function __construct(ClassManagementRepository $classRepository)
    {
        $this->classRepository = $classRepository;
    }

    public function getClassesList($search): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->classRepository->getClassesListData($search);
    }

    public function getTeachersList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->classRepository->getTeachersListData();
    }

    public function updateClass(?string $class_id, ?string $teacher_id, ?string $academic_year)
    {
        return $this->classRepository->updateClassData($class_id, $teacher_id,$academic_year);
    }

    public function getClassById($id)
    {
        return  $this->classRepository->getClassByIdData($id);
    }


}
