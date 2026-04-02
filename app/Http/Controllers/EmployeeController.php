<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = Employee::query()
            ->latest()
            ->get()
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'designation' => $employee->designation,
                'department' => $employee->department,
                'status' => $employee->status,
                'hired_at' => optional($employee->hired_at)->toDateString(),
                'notes' => $employee->notes,
                'created_at' => optional($employee->created_at)->toDateTimeString(),
            ])
            ->values();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Employee::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()?->id,
        ]);

        return redirect()->route('employees.index')
            ->with('status', 'Employee created successfully.');
    }

    public function edit(int $employee): Response
    {
        $employeeRecord = Employee::query()->findOrFail($employee);

        return Inertia::render('Employees/Edit', [
            'employee' => [
                'id' => $employeeRecord->id,
                'name' => $employeeRecord->name,
                'email' => $employeeRecord->email,
                'phone' => $employeeRecord->phone,
                'designation' => $employeeRecord->designation,
                'department' => $employeeRecord->department,
                'status' => $employeeRecord->status,
                'hired_at' => optional($employeeRecord->hired_at)->toDateString(),
                'notes' => $employeeRecord->notes,
            ],
        ]);
    }

    public function update(EmployeeRequest $request, int $employee): RedirectResponse
    {
        $employeeRecord = Employee::query()->findOrFail($employee);
        $employeeRecord->update($request->validated());

        return redirect()->route('employees.index')
            ->with('status', 'Employee updated successfully.');
    }

    public function destroy(int $employee): RedirectResponse
    {
        $employeeRecord = Employee::query()->findOrFail($employee);
        $employeeRecord->delete();

        return redirect()->route('employees.index')
            ->with('status', 'Employee deleted successfully.');
    }
}
