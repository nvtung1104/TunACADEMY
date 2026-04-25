<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;

class GradeController extends Controller
{
    public function index()
    {
        return $this->success(Grade::withCount('classrooms')->orderBy('order_index')->get());
    }

    public function show(Grade $grade)
    {
        return $this->success($grade->loadCount('classrooms'));
    }
}
