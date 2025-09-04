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

    public function getClassesList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->classRepository->getClassesListData();
    }

    public function getTeachersList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->classRepository->getTeachersListData();
    }

    public function assignNewTeacherToClass(?string $class_id, ?string $teacher_id)
    {
        return $this->classRepository->assignNewTeacherToClassData($class_id, $teacher_id);
    }

    public function getClassById($id)
    {
        return  $this->classRepository->getClassByIdData($id);
    }


}
