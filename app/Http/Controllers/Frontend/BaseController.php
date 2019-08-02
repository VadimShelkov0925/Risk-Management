<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\App;

use Illuminate\Http\Request;

use App\Models\FileItem;

use Session;
use Mail;
use Storage;

class BaseController extends Controller
{
    //delete file by id
    public function deleteFile( Request $request ){
        return FileItem::destroy((int)$request->get('id'));
    }

    public function uploadFile( Request $request ){
        if($file = $request->file('Filedata'))
            FileItem::saveFile($file);
        return 1;
    }

    public function newAreaHtml( Request $request ){
        $sort = FileItem::getSortArray();
        $files = FileItem::with(['type'])
            ->leftJoin('file_types', 'files.type_id', '=', 'file_types.id')
            ->select('file_types.extension', 'files.*')
            ->orderBy($sort['column'], $sort['by'])
            ->get();

        $files = FileItem::setImages($files);

        return view('frontend._part._files', compact(['files']))->render();
    }

    public function newsort( Request $request ){
        Session::put('sort', $request->get('sorting'));

        return $this->newAreaHtml($request);
    }

    public function sendmail( Request $request ){
        $from_email = env('MAIL_FROM_ADDRESS', __('a@pampuni.com'));
        $to_email  =  $request->get('mail');
        $fileId = (int) $request->get('fileId');
        $file   = FileItem::whereId($fileId)->first();
        $path = public_path('uploads/frontend/'.$file->filename);

        $data = [
            'from_email' => $from_email,
            'to_mail' => $to_email,
            'filename' => $file->name,
            'path' => $path,
            'subjest' => $request->get('subjest'),
            'body' => $request->get('message'),
            "locale" => App::getLocale()
        ];

        Mail::send('emails.sendpdf', $data,  function($message) use ($data){
            $message->from($data['from_email']);
            $message->to($data['to_mail']);
            $message->subject($data['subjest']);
            $message->attach($data['path']);
        });

        return json_encode(['status' => __('ok')]);
    }
}