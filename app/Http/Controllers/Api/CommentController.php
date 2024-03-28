<?php

namespace App\Http\Controllers\Api;

use App\Models\comment;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Create a new CommentController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Create a new CommentController instance.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'task_id' => 'required',
        ]);
        $comment = comment::create($request->all());
        $comment->user_id = auth()->user()->id;
        $comment->save();
        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * Display a listing of the comments.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return comment::all();
    }

    /**
     * Display the specified comment.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return comment::find($id);
    }

    /**
     * Update the specified comment in the database.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = comment::find($id);
        $comment->update($request->all());
        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ], 200);
    }

    /**
     * Remove the specified comment from the database.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        comment::find($id)->delete();
        return response()->json([
            'message' => 'Comment deleted successfully'
        ], 204);
    }
}
