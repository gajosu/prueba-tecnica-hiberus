import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card'
import { Package, Plus } from 'lucide-react'
import api from '@/lib/api'

export default function AdminProductsPage() {
  const [showForm, setShowForm] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const navigate = useNavigate()

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    price: '',
    stock: '',
    imageUrl: '',
  })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setSuccess('')
    setLoading(true)

    try {
      await api.post('/products', {
        name: formData.name,
        description: formData.description,
        price: parseFloat(formData.price),
        currency: 'USD',
        stock: parseInt(formData.stock),
        imageUrl: formData.imageUrl || null,
      })

      setSuccess('¡Producto creado exitosamente!')
      
      // Reset form
      setFormData({
        name: '',
        description: '',
        price: '',
        stock: '',
        imageUrl: '',
      })
      
      // Close form after 2 seconds
      setTimeout(() => {
        setShowForm(false)
        setSuccess('')
      }, 2000)
    } catch (err) {
      if (err.response?.data?.errors) {
        // Laravel-style validation errors (422)
        const errors = Object.values(err.response.data.errors).flat()
        setError(errors.join(', '))
      } else {
        setError(err.response?.data?.message || err.response?.data?.error || 'Error al crear el producto')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Administración de Productos</h1>
          <p className="text-muted-foreground">Gestiona el catálogo de productos</p>
        </div>
        <Button onClick={() => setShowForm(!showForm)}>
          <Plus className="w-4 h-4 mr-2" />
          {showForm ? 'Cancelar' : 'Nuevo Producto'}
        </Button>
      </div>

      {error && (
        <div className="bg-destructive/10 text-destructive px-4 py-3 rounded-md">
          {error}
        </div>
      )}

      {success && (
        <div className="bg-green-50 text-green-700 px-4 py-3 rounded-md border border-green-200">
          {success}
        </div>
      )}

      {showForm && (
        <Card>
          <CardHeader>
            <CardTitle>Crear Nuevo Producto</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium">Nombre del Producto *</label>
                  <Input
                    type="text"
                    placeholder="Ej: Laptop Dell XPS 13"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    required
                    minLength={3}
                    disabled={loading}
                  />
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">URL de Imagen</label>
                  <Input
                    type="url"
                    placeholder="https://example.com/image.jpg"
                    value={formData.imageUrl}
                    onChange={(e) => setFormData({ ...formData, imageUrl: e.target.value })}
                    disabled={loading}
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Descripción</label>
                <textarea
                  className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 min-h-[100px]"
                  placeholder="Descripción detallada del producto"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  disabled={loading}
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium">Precio (USD) *</label>
                  <Input
                    type="number"
                    step="0.01"
                    min="0.01"
                    placeholder="99.99"
                    value={formData.price}
                    onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                    required
                    disabled={loading}
                  />
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium">Stock *</label>
                  <Input
                    type="number"
                    min="0"
                    placeholder="100"
                    value={formData.stock}
                    onChange={(e) => setFormData({ ...formData, stock: e.target.value })}
                    required
                    disabled={loading}
                  />
                </div>
              </div>

              <div className="flex gap-2 pt-4">
                <Button type="submit" disabled={loading} className="flex-1">
                  <Package className="w-4 h-4 mr-2" />
                  {loading ? 'Creando...' : 'Crear Producto'}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setShowForm(false)}
                  disabled={loading}
                >
                  Cancelar
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      {!showForm && (
        <Card>
          <CardContent className="p-12 text-center">
            <Package className="w-16 h-16 mx-auto mb-4 text-muted-foreground" />
            <h3 className="text-lg font-semibold mb-2">Panel de Administración</h3>
            <p className="text-muted-foreground mb-4">
              Haz clic en "Nuevo Producto" para agregar productos al catálogo
            </p>
            <Button variant="outline" onClick={() => navigate('/catalog')}>
              Ver Catálogo
            </Button>
          </CardContent>
        </Card>
      )}
    </div>
  )
}

