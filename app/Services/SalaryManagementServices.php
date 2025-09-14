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

    public function getMonthlySalaryList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection,mixed $month,mixed $year)
    {
        return $this->salaryManagementRepository->getMonthlySalaryListData($search, $perPage, $sortedBy, $sortDirection,$month, $year);
    }

    public function getTeachersList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getTeachersListData();
    }

    public function getStaffList()
    {
        return $this->salaryManagementRepository->getStaffListData();
    }

    public function getSalaryStructures()
    {
        return $this->salaryManagementRepository->getSalaryStructuresData();
    }

    public function getSalaryDeductions(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->salaryManagementRepository->getSalaryDeductionsData();
    }

    public function saveTeacherSalary(mixed $teacher_id, mixed $salary_structure_id, mixed $account, mixed $payment_method): bool|string
    {
        $month = now()->month;

        $structure = $this->salaryManagementRepository->getSalaryStructureByIdData($salary_structure_id);

        $deduction = $this->salaryManagementRepository->getSalaryDeductionByEmployeeIdData($teacher_id,$month);

        $totalDeduction = optional($deduction)->sum(function ($item) {
            return (floatval($item->amount) * intval($item->multiples));
        }) ?? 0;

        $salary = new Salary();
        $newSalary = [
            'teacher_id' => $teacher_id,
            'salary_structure_id' => $salary_structure_id,
            'gross_salary' => $structure->gross_salary,
            'total_deduction' => $totalDeduction,
            'net_salary' => $structure->gross_salary - $totalDeduction,
            'payment_date' => now()->addMonth()->startOfMonth(),
            'account' => $account,
            'payment_method' => $payment_method,
            'status' => 'Pending',

        ];

        $salary->fill($newSalary);
        return $this->salaryManagementRepository->saveSalaryData($salary);
    }

    public function saveStaffSalary(mixed $staff_id, mixed $salary_structure_id, mixed $account, mixed $payment_method): bool|string
    {
        $month = now()->month;

        $structure = $this->salaryManagementRepository->getSalaryStructureByIdData($salary_structure_id);
        $deduction = $this->salaryManagementRepository->getSalaryDeductionByEmployeeIdData($staff_id,$month);

        $totalDeduction = optional($deduction)->sum(function ($item) {
            return (floatval($item->amount) * intval($item->multiples));
        }) ?? 0;

        $salary = new Salary();
        $newSalary = [
            'staff_id' => $staff_id,
            'salary_structure_id' => $salary_structure_id,
            'gross_salary' => $structure->gross_salary,
            'total_deduction' => $totalDeduction,
            'net_salary' => $structure->gross_salary - $totalDeduction,
            'payment_date' => now()->addMonth()->startOfMonth(),
            'account' => $account,
            'payment_method' => $payment_method,
            'status' => 'Pending',

        ];

        $salary->fill($newSalary);
        return $this->salaryManagementRepository->saveSalaryData($salary);
    }

}
