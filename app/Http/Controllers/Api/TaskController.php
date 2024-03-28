<?php

namespace App\Http\Controllers\Api;

use App\Models\tasks;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Create a new TaskController instance.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
            'user_id' => 'required'
        ]);
        $task = tasks::create($request->all());
        return response()->json($task, 201);
    }

    /**
     * Display a listing of the tasks.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return tasks::all();
    }

    /**
     * Display the specified task.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return tasks::find($id);
    }

    /**
     * Update the specified task in the database.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = tasks::find($id);
        $task->update($request->all());
        return response()->json($task, 200);
    }

    /**
     * Remove the specified task from the database.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        tasks::find($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * Assign a user to a task.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        $task = Tasks::find($request->id);
        $task->user_id = $request->user_id;
        $task->save();
        return response()->json($task, 200);
    }

    /**
     * Modify the status of a task.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function modifyStatus(Request $request)
    {
        $task = Tasks::find($request->id);
        $firstRoleName = auth()->user()->roles->first()->name;
        if ($task->user_id !== $request->user_id || $firstRoleName !== 'admin') {
            return response()->json(['message' => 'You are not authorized to modify this task'], 401);
        }
        $task->status = $request->status;
        $task->save();
        return response()->json($task, 200);
    }
}
