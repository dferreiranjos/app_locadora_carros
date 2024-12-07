<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use Hamcrest\Core\HasToString;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    private $modelo;

    public function __construct(Modelo $modelo)
    {   
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->get('atributos'));
        // dd($request->atributos);
        // return response()->json($this->modelo->with('marca')->get(), 200);

        $modelos = array();

        if($request->has('atributos_marca')){
            $atributos_marca = $request->atributos_marca;
            $modelos =  $this->modelo->with('marca:id,'.$atributos_marca);
        }else{
            $modelos = $this->modelo->with('marca');
        }

        if($request->has('filtro')){
            // dd(explode(":",$request->filtro));
            // dd($request->filtro);
            $filtros = explode(';', $request->filtro);
            // dd($filtros);
            foreach($filtros as $key => $condicao){
                $c = explode(':', $condicao);
                $modelos = $modelos->where($c[0], $c[1], $c[2]);
            }
        }

        if($request->has('atributos')){
            $atributos = $request->atributos;
            // $modelos = $this->modelo->select('id', 'nome', 'imagem')->get();
            $modelos = $modelos->selectRaw($atributos)->get();
        }else{
            $modelos = $modelos->get();
        }
        // dd($modelos->toSql(), $modelos->getBindings()); // só funciona se tirar os get()

        return response()->json($modelos, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');
        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas, 
            'lugares' => $request->lugares, 
            'air_bag' => $request->air_bag, 
            'abs' => $request->abs
        ]);
        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if($modelo === null){
            return response()->json(['erro'=>'Recurso pesquisado não existe'], 404);
        }
        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $modelo = $this->modelo->find($id);

        if($modelo === null){
            return response()->json(['erro'=>'Recurso não encontrado. Não é possível atualizar'], 404);
        }

        if($request->method() === 'PATCH'){
            
            $regrasDinamicas = array();

            foreach($modelo->rules() as $input => $regra){
                
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }
           
            $request->validate($regrasDinamicas);
        }else{
            $request->validate($modelo->rules());
        }
        
        if($request->file('imagem')){
            Storage::disk('public')->delete($modelo->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;

        $modelo->save();

        /*$modelo->update([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas, 
            'lugares' => $request->lugares, 
            'air_bag' => $request->air_bag, 
            'abs' => $request->abs
        ]);*/
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);

        if($modelo === null){
            return response()->json(['erro' => 'Recurso não encontrado. Não é possível deletar'], 404);
        }

        
        Storage::disk('public')->delete($modelo->imagem);
        

        $modelo->delete();
        return response()->json(['msg' => 'O modelo foi removido com sucesso!'], 200);
    }
}
