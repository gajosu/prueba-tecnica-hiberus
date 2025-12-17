import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useCart } from '@/context/CartContext'
import { createOrder } from '@/lib/api'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { ShoppingCart, Trash2, Minus, Plus, Package } from 'lucide-react'

export default function CartPage() {
  const { cart, removeFromCart, updateQuantity, clearCart, getTotalPrice } = useCart()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const navigate = useNavigate()

  const handleCheckout = async () => {
    if (cart.length === 0) return

    setLoading(true)
    setError('')

    try {
      const items = cart.map((item) => ({
        productId: item.id,
        quantity: item.quantity,
      }))

      const response = await createOrder(items)
      clearCart()
      navigate(`/orders/${response.id}`)
    } catch (err) {
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat()
        setError(errors.join(', '))
      } else {
        setError(err.response?.data?.message || err.response?.data?.error || 'Error al crear el pedido')
      }
    } finally {
      setLoading(false)
    }
  }

  if (cart.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <ShoppingCart className="w-16 h-16 mx-auto mb-4 text-muted-foreground" />
          <h3 className="text-lg font-semibold mb-2">Tu carrito está vacío</h3>
          <p className="text-muted-foreground mb-4">Agrega productos desde el catálogo</p>
          <Button onClick={() => navigate('/catalog')}>Ir al catálogo</Button>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Carrito de Compras</h1>
        <p className="text-muted-foreground">Revisa tus productos antes de finalizar</p>
      </div>

      {error && (
        <div className="bg-destructive/10 text-destructive px-4 py-3 rounded-md">
          {error}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-4">
          {cart.map((item) => (
            <Card key={item.id}>
              <CardContent className="p-6">
                <div className="flex items-start gap-4">
                  {item.image_url && (
                    <div className="w-24 h-24 flex-shrink-0 overflow-hidden rounded-md bg-gray-100">
                      <img
                        src={item.image_url}
                        alt={item.name}
                        className="h-full w-full object-cover object-center"
                        onError={(e) => {
                          e.target.style.display = 'none'
                        }}
                      />
                    </div>
                  )}
                  <div className="flex-1">
                    <h3 className="font-semibold text-lg mb-1">{item.name}</h3>
                    <p className="text-sm text-muted-foreground mb-3">
                      {item.description || 'Sin descripción'}
                    </p>
                    <div className="flex items-center gap-4">
                      <div className="flex items-center gap-2">
                        <Button
                          size="icon"
                          variant="outline"
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                        >
                          <Minus className="w-4 h-4" />
                        </Button>
                        <span className="w-12 text-center font-semibold">{item.quantity}</span>
                        <Button
                          size="icon"
                          variant="outline"
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                          disabled={item.quantity >= item.stock}
                        >
                          <Plus className="w-4 h-4" />
                        </Button>
                      </div>
                      <Button
                        size="icon"
                        variant="ghost"
                        onClick={() => removeFromCart(item.id)}
                        className="text-destructive"
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-muted-foreground">Precio unitario</p>
                    <p className="font-semibold">${item.price}</p>
                    <p className="text-sm text-muted-foreground mt-2">Subtotal</p>
                    <p className="text-xl font-bold text-primary">
                      ${(item.price * item.quantity).toFixed(2)}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="lg:col-span-1">
          <Card className="sticky top-6">
            <CardHeader>
              <CardTitle>Resumen del Pedido</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Productos ({cart.length})</span>
                  <span className="font-medium">${getTotalPrice().toFixed(2)}</span>
                </div>
                <div className="border-t pt-2">
                  <div className="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span className="text-primary">${getTotalPrice().toFixed(2)}</span>
                  </div>
                </div>
              </div>

              <Button
                onClick={handleCheckout}
                disabled={loading}
                className="w-full"
                size="lg"
              >
                <Package className="w-4 h-4 mr-2" />
                {loading ? 'Procesando...' : 'Crear Pedido'}
              </Button>

              <Button
                variant="outline"
                onClick={() => navigate('/catalog')}
                className="w-full"
              >
                Seguir comprando
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}

