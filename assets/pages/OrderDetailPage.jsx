import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { getOrderDetail, checkoutOrder } from '@/lib/api'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/Card'
import { CheckCircle, Package, CreditCard, ArrowLeft } from 'lucide-react'

export default function OrderDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [order, setOrder] = useState(null)
  const [loading, setLoading] = useState(true)
  const [processing, setProcessing] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    loadOrder()
  }, [id])

  const loadOrder = async () => {
    try {
      setLoading(true)
      const data = await getOrderDetail(id)
      setOrder(data)
    } catch (error) {
      setError(error.response?.data?.error || 'Error al cargar el pedido')
    } finally {
      setLoading(false)
    }
  }

  const handleCheckout = async () => {
    setProcessing(true)
    setError('')

    try {
      await checkoutOrder(id)
      await loadOrder() // Reload order to get updated status
    } catch (err) {
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat()
        setError(errors.join(', '))
      } else {
        setError(err.response?.data?.message || err.response?.data?.error || 'Error al procesar el pago')
      }
    } finally {
      setProcessing(false)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <Package className="w-12 h-12 mx-auto mb-4 text-muted-foreground animate-pulse" />
          <p className="text-muted-foreground">Cargando pedido...</p>
        </div>
      </div>
    )
  }

  if (error && !order) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <Package className="w-16 h-16 mx-auto mb-4 text-destructive" />
          <h3 className="text-lg font-semibold mb-2">Error</h3>
          <p className="text-muted-foreground mb-4">{error}</p>
          <Button onClick={() => navigate('/catalog')}>Volver al catálogo</Button>
        </div>
      </div>
    )
  }

  const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
  }

  const statusLabels = {
    pending: 'Pendiente de Pago',
    paid: 'Pagado',
    processing: 'En Proceso',
    completed: 'Completado',
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/catalog')}>
          <ArrowLeft className="w-5 h-5" />
        </Button>
        <div>
          <h1 className="text-3xl font-bold">Detalle del Pedido</h1>
          <p className="text-muted-foreground">ID: {order.id}</p>
        </div>
      </div>

      {error && (
        <div className="bg-destructive/10 text-destructive px-4 py-3 rounded-md">
          {error}
        </div>
      )}

      {order.status === 'paid' && (
        <div className="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
          <CheckCircle className="w-6 h-6 text-green-600" />
          <div>
            <h3 className="font-semibold text-green-900">¡Pago exitoso!</h3>
            <p className="text-sm text-green-700">Tu pedido ha sido procesado correctamente</p>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Productos</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {order.items.map((item, index) => (
                <div key={index} className="flex justify-between items-start py-3 border-b last:border-0">
                  <div className="flex-1">
                    <h4 className="font-semibold">{item.product_name}</h4>
                    <p className="text-sm text-muted-foreground">
                      Cantidad: {item.quantity} × ${item.unit_price}
                    </p>
                  </div>
                  <div className="text-right font-semibold text-primary">
                    ${item.subtotal.toFixed(2)}
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>

        <div className="lg:col-span-1">
          <Card className="sticky top-6">
            <CardHeader>
              <CardTitle>Resumen</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Estado</span>
                  <span className={`px-2 py-1 rounded-full text-xs font-semibold ${statusColors[order.status]}`}>
                    {statusLabels[order.status]}
                  </span>
                </div>
                <div className="border-t pt-2">
                  <div className="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span className="text-primary">${order.total.toFixed(2)}</span>
                  </div>
                </div>
              </div>

              {order.status === 'pending' && (
                <Button
                  onClick={handleCheckout}
                  disabled={processing}
                  className="w-full"
                  size="lg"
                >
                  <CreditCard className="w-4 h-4 mr-2" />
                  {processing ? 'Procesando pago...' : 'Pagar Ahora'}
                </Button>
              )}

              <Button
                variant="outline"
                onClick={() => navigate('/catalog')}
                className="w-full"
              >
                Volver al catálogo
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}

