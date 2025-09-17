<?php

namespace App\Services;

use App\Models\Salary;
use App\Models\SalaryDeduction;
use App\Models\SalaryStructure;
use App\Repositories\SalaryManagementRepository;

class SalaryManagementServices
{
    protected $salaryManagementRepository;

    public function __construct(SalaryManagementRepository $salaryManagementRepository)
    {
        $this->salaryManagementRepository = $salaryManagementRepository;
    }

    public function getSalaryStructureList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        return $this->salaryManagementRepository->getSalaryStructureList($search, $perPage, $sortedBy, $sortDirection);
    }

    public function saveSalaryStructure(array $structure): bool|string
    {
        $salary = new SalaryStructure();
        $salary->fill($structure);

        return $this->salaryManagementRepository->saveSalaryStructureData($salary);
    }

    public function getSalaryStructureById(int $id): SalaryStructure
    {
        return $this->salaryManagementRepository->getSalaryStructureByIdData($id);
    }

    public function getSalaryDeductionList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        return $this->salaryManagementRepository->getSalaryDeductionListData($search, $perPage, $sortedBy, $sortDirection);
    }

    public function saveSalaryDeduction(array $structure): bool|string
    {
        $deduction = new SalaryDeduction();
        $deduction->fill($structure);

        return $this->salaryManagementRepository->saveSalaryDeductionData($deduction);
    }

    public function getSalaryDeductionById(int $id): SalaryDeduction
    {
        return $this->salaryManagementRepository->getSalaryDeductionByIdData($id);
    }

    public function getSalaryDeductionByEmployeeId(string $id,mixed $month)
    {
        return $this->salaryManagementRepository->getSalaryDeductionByEmployeeIdData($id,$month);
    }

    public function getMonthlySalaryList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection,mixed $month,mixed $year)
    {
        return $this->salaryManagementRepository->getMonthlySalaryListData($search, $perPage, $sortedBy, $sortDirection,$month, $year);
    }

    public function getTeachersList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getTeachersListData();
    }

    public function getStaffList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getStaffListData();
    }

    public function getSalaryStructures(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getSalaryStructuresData();
    }

    public function getSalaryDeductions(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getSalaryDeductionsData();
    }

    public function saveSalary(Salary $salary): bool|string
    {
        return $this->salaryManagementRepository->saveSalaryData($salary);
    }

    public function getSalary(mixed $id)
    {
        return $this->salaryManagementRepository->getSalaryData($id);
    }

}
