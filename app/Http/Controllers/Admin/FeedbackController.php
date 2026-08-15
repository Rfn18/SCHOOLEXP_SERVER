<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::paginate(10);
        if ($feedbacks->isEmpty())
        {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => null,
            ], 404);
        }
        
        return new ApiResource(
            true,
            'Data berhasil ditemukan',
            $feedbacks,
        );
    }

    public function destroy(string $anonymous_code)
    {
        $feedback = Feedback::where('anonymous_code', $anonymous_code)->first();
        if ($feedback == null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $feedback->delete();
        return new ApiResource(
            true,
            'Data berhasil dihapus',
            null,
        );
    }

}
