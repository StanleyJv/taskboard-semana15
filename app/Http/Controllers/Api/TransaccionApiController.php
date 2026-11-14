<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarTransaccionApiRequest;
use App\Http\Resources\TransaccionResource;
use App\Models\Transaccion;
use Illuminate\Http\Request;

class TransaccionApiController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado');

        $transacciones = Transaccion::whereHas(
            'comercio',
            fn ($q) => $q->where(
                'user_id',
                $request->user()->id
            )
        )
            ->when(
                $estado,
                fn ($q) => $q->where('estado', $estado)
            )
            ->latest()
            ->get();

        return TransaccionResource::collection($transacciones);
    }

    public function store(GuardarTransaccionApiRequest $request)
    {
        $datos = $request->validated();

        $transaccion = Transaccion::create([
            'comercio_id' => $datos['comercio_id'],
            'cliente_nombre' => $datos['cliente_nombre'],
            'monto' => $datos['monto'],
            'moneda' => 'USD',
            'metodo_pago' => 'No especificado',
            'estado' => $datos['estado'] ?? 'Iniciada',
        ]);

        return (new TransaccionResource($transaccion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Transaccion $transaccion)
    {
        $this->autorizar($transaccion);

        return new TransaccionResource($transaccion);
    }

    public function destroy(Transaccion $transaccion)
    {
        $this->autorizar($transaccion);

        $transaccion->delete();

        return response()->noContent();
    }

    private function autorizar(Transaccion $transaccion): void
    {
        abort_unless(
            $transaccion->comercio->user_id === auth()->id(),
            404
        );
    }
}