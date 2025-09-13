<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\FeeStructure;
use App\Repositories\AccountManagementRepository;
use App\Repositories\StudentManagementRepository;
use Carbon\Carbon;

class AccountManagementServices
{
    protected AccountManagementRepository $accountManagementRepository;
    protected StudentManagementRepository $studentManagementRepository;

    protected array $classMapping = [
        'Junior' => ['Nursery', 'Prep'],
        'Primary' => ['I', 'II', 'III', 'IV', 'V'],
        'Middle'  => ['VI', 'VII', 'VIII'],
        'Secondary' => ['IX', 'X'],
    ];

    function __construct(AccountManagementRepository $accountManagementRepository, StudentManagementRepository $studentManagementRepository)
    {
        $this->accountManagementRepository = $accountManagementRepository;
        $this->studentManagementRepository = $studentManagementRepository;
    }

    public function getFeeStructureList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        return $this->accountManagementRepository->getFeeStructureListData($search, $perPage, $sortedBy, $sortDirection);
    }

    public function saveFeeStructure(array $feeForm): bool|string
    {
        $feeStructure = new FeeStructure();
        $feeStructure->fill($feeForm);
        return $this->accountManagementRepository->saveFeeStructureData($feeStructure);
    }

    public function getFeeStructureById($feeId)
    {
        return $this->accountManagementRepository->getFeeStructureByIdData($feeId);
    }

    public function updateFeeStructure(array $feeForm, mixed $id)
    {
        return $this->accountManagementRepository->updateFeeStructureData($feeForm, $id);
    }

    public function deleteFeeStructure($id): int|string
    {
        return $this->accountManagementRepository->deleteFeeStructureData($id);
    }

    public function getOneTimeFeeStructure($class)
    {
        $parentClass = $this->mapToParentClass($class);
        return $this->accountManagementRepository->getOneTimeFeeStructureData($parentClass);
    }

    public function getAnnualStructure($class)
    {
        $parentClass = $this->mapToParentClass($class);
        return $this->accountManagementRepository->getAnnualStructureData($parentClass);
    }

    protected function mapToParentClass(string $class)
    {
        foreach ($this->classMapping as $parent => $children) {
            if (in_array($class, $children, true)) {
                return $parent;
            }
        }
        return $classMapping[$class] ?? [];
    }

    //---------------------------------------Fee Structures End Here---------------------------------------------//

    public function generateFees(mixed $month, int $year): bool|string
    {
        $response = false;
        $feeStructure = $this->accountManagementRepository->getAllFeeStructureData();

        $monthlyFeeStructures = $feeStructure->where('frequency', 'Monthly');

        foreach ($monthlyFeeStructures as $structure) {
            $studentClasses = match ($structure->class) {
                'Junior' => ['Nursery', 'Prep'],
                'Primary' => ['I', 'II', 'III', 'IV', 'V'],
                'Middle' => ['VI', 'VII', 'VIII'],
                'Secondary' => ['IX', 'X'],
                default => [],
            };
            $students = $this->studentManagementRepository->getAllStudentData();
            $classStudents = $students->whereIn('class', $studentClasses);
            foreach ($classStudents as $student) {
                // check if already generated for this month
                $check = $this->accountManagementRepository->getMonthFeeByStudentId($student->student_id,$structure->id, $month, $year);
                if (!$check) {
                    $fee = new Fee();
                    $newFee = ['student_id' => $student->student_id,'fee_structure_id' => $structure->id,
                        'amount' => $structure->amount,'due_date' => Carbon::create($year, $month, 10),
                        'pending_amount' => $structure->amount,'status' => 'pending'];
                    $fee->fill($newFee);
                    $this->accountManagementRepository->saveMonthlyFeeData($fee);
                    $response = true;
                }
            }

        }
        return $response ? true : 'No new fees were generated';
    }

    public function getMonthlyFeeList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection, $month, $year)
    {
        return $this->accountManagementRepository->getMonthlyFeeListData($search, $perPage, $sortedBy, $sortDirection, $month, $year);
    }

    public function getMonthlyFeeById($feeId)
    {
        return $this->accountManagementRepository->getMonthlyFeeById($feeId);
    }

    public function addOneTimeFee(array $fee): bool|string
    {
        $oneTimeFee = new Fee();
        $oneTimeFee->fill($fee);
        return $this->accountManagementRepository->addOneTimeFeeData($oneTimeFee);
    }

    public function getMonthlyFeesByStudentAndMonth($studentId, $month)
    {
        return $this->accountManagementRepository->getMonthlyFeesByStudentAndMonthData($studentId, $month);
    }



}
