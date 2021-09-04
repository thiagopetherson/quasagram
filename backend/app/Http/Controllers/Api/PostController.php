<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response; // Chamando a classe de respostas (como as de erros por exemplo)

use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        $data = Post::orderBy('created_at','desc')->get();        
        return response()->json(['posts' =>  $data], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
       
        $dataform = [];            
        $dataform['caption'] = $request->caption;
        $dataform['location'] = $request->location;     
               
        $post = new Post;


        $file = $request->file('image');    
        
       
        // Se informou o arquivo, retorna um boolean
        $fileExists = $request->hasFile('image');
        if(!$fileExists) {
            return response()->json(['error' => 'Não é um arquivo de imagem válido! [s4s4s]'], 404);
        }

        // Retorna mime type do arquivo (Exemplo image/png)
        $mimeType = $request->image->getMimeType();

        $arrayMimeTypes = ['image/png','image/jpg','image/jpeg'];

        if(!in_array($mimeType,  $arrayMimeTypes))
        {
            return response()->json(['error' => 'Não é um arquivo de imagem válido! [g4hg4h5]'], 404);
        }

        // Extensão do arquivo
        $extension = $request->image->getClientOriginalExtension();
        
        /*
        $arrayExtensions = ['PNG','JPG','JPEG','png','jpg','jpeg'];

        
        if(!in_array($extension, $arrayExtensions))
        {
            return response()->json(['error' => 'O arquivo não tem uma extensão válida!'], 404);
        }
        */

        // Tamanho do arquivo
        $fileSize = $request->image->getSize();

        if($fileSize > 2097152) {
            return response()->json(['error' => 'O tamanho do arquivo ultrapassa o limite de 2MB!'], 404);
        }

        //Verificando se é um arquivo válido
        $isValid = $request->file('image')->isValid();

        if($isValid != 1) {
            return response()->json(['error' => 'Não é um arquivo de imagem válido! [wewe878]'], 404);
        }        

        $imageName = md5(rand(1, 9999999999999999)) . '.jpg';

        //Movendo o arquivo da pasta temporária para storage (que tem um espelho em public)
        $upload = $request->image->storeAs('media/uploads-posts-images', $imageName);

        $dataform['image'] = $upload;

        if($upload AND $post->create($dataform)) {            
             return response()->json(['success' => 'Imagem Inserida com Sucesso'], Response::HTTP_OK);
        }

        return response()->json(['error' => 'A imagem não foi inserida. Tente novamente ou entre em contato com o suporte!'], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
