<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use App\Repositories\MarcaRepository;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private $marca;

    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }

    public function index(Request $request)
    {

        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')){
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        }else{
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        if($request->has('filtro')){
            $marcaRepository->filtro($request->filtro);
        }

        if($request->has('atributos')){
            $marcaRepository->selectAtributos($request->atributos);
        }

        // ------------------------------------------------------------------------------
        // $marcas = array();

        // if($request->has('atributos_modelos')){
        //     $atributos_modelos = $request->atributos_modelos;
        //     $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        // }else{
        //     $marcas = $this->marca->with('modelos');
        // }

        // if($request->has('filtro')){
        //     $filtros = explode(';', $request->filtro);
        //     foreach($filtros as $key => $condicao){
        //         $c = explode(':', $condicao);
        //         // dd($c);
        //         $marcas = $marcas->where($c[0], $c[1], $c[2]);
        //     }
        // }

        // if($request->has('atributos')){
        //     $atributos = $request->atributos;
        //     // dd($atributos);
        //     $marcas = $marcas->selectRaw($atributos)->get();
        // }else{
        //     $marcas = $marcas->get();
        // }

        // // $marcas = $this->marca->with('modelos')->get();
        // return response()->json($marcas, 200);

        return response()->json($marcaRepository->getResultado(), 200);
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
        // Como está sendo feita uma validação é necessário no headers do postman mandar a chama Accept e value application/json
        $request->validate($this->marca->rules(), $this->marca->feedback());
        // stateless
        // dd($request->nome);
        // dd($request->get('nome'));
        // dd($request->input('nome'));
        // dd($request->imagem);
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');
        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['erro'=>'Recurso pesquisado não existe'], 404);
        }
        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro'=>'Recurso não encontrado. Não é possível atualizar'], 404);
        }

        if($request->method() === 'PATCH'){
            
            $regrasDinamicas = array();

            foreach($marca->rules() as $input => $regra){
                
                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }
            }
           
            $request->validate($regrasDinamicas, $marca->feedback());
        }else{
            $request->validate($marca->rules(), $marca->feedback());
        }
        
        if($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        // preenche o objeto marca com os dados do request
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        // dd($marca->getAttributes());
        $marca->save();

        /*$marca->update([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);*/
        
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'Recurso não encontrado. Não é possível deletar'], 404);
        }

        
        Storage::disk('public')->delete($marca->imagem);
        

        $marca->delete();
        return response()->json(['msg' => 'Marca removida com sucesso!'], 200);
    }
}
