<?php

namespace App\Http\Controllers;

use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Question;
use App\Models\Cybersecurity;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\AnswerQuestion;
use App\Models\Grc;

class NewGrcController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('user.login');
        }

         $data = Grc::get();

         return view('front.grc.index',['items'=>$data]);

    }//end of index

    public function grcDetails($id)
    {
        $AnswerQuestionCount = AnswerQuestion::where('grc_id', $id)
                                             ->where('user_id', auth()->id())
                                             ->get();

        $question  = Question::where('cybersecurity_id','=',$id)->get();
        $questions = Question::where('cybersecurity_id','=',$id)->with('answers')->get();
        $answer    = Answer::all();

        $data     = Grc::findOrFail($id);
        $comments = Comment::where('grc_id','=',$id)->get();

        return view('front.grc.detials', [
             'item'       =>$data,
             'comments'   =>$comments,
             'questions'  =>$question,
             'questionss' =>$questions,
             'answers'    =>$answer,
             'iid'        =>$id,
        ]);

    }//end of fun

    public function answer_to_GRC(Request $request)
    {
        // return $request->all();

        $data = AnswerQuestion::where('level_id',$request->iid)->first();

        if (!$data) {

            if (!$request->ii) {
                
                foreach ($request->answer_id as $key=>$data) {

                    AnswerQuestion::create([
                        'question_id' => $key,
                        'answer_id'   => $data,
                        'user_id'     => auth()->id(),
                        'grc_id'     => $request->iid,
                    ]);
                }

                return redirect()->route('grcPages');
            }
        }

        $grc_id    = AnswerQuestion::latest()->first()->grc_id;
        $question_id = AnswerQuestion::where('grc_id',$grc_id)->pluck('question_id')->unique();
        $answers     = AnswerQuestion::where('grc_id',$grc_id)->pluck('answer_id')->unique();
        $answe_count = Answer::whereIn('id', $answers)->where('answer_question_id',1)->count();

        $data = Question::with('answers')->whereIn('id',$question_id)->get();

        $data_count = Question::with('answers')->whereIn('id',$question_id)->count();

        return view('front.grc.index',[
            'items'=>$data,
            'data_count'=>$data_count,
            'answers'=>$answers,
            'answe_count'=>$answe_count,
        ]);

    }//end of answer_to

}//end of controller
