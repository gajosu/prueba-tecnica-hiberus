import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { getProducts } from '@/lib/api'
import { useCart } from '@/context/CartContext'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/Card'
import { ShoppingCart, Search, Package } from 'lucide-react'

export default function CatalogPage() {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [addedToCart, setAddedToCart] = useState(null)
  const { addToCart } = useCart()
  const navigate = useNavigate()

  useEffect(() => {
    loadProducts()
  }, [page, search])

  const loadProducts = async () => {
    try {
      setLoading(true)
      const data = await getProducts({ search, page, limit: 12 })
      setProducts(data.data || [])
      setTotalPages(data.meta?.total_pages || 1)
    } catch (error) {
      console.error('Error loading products:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSearch = (e) => {
    e.preventDefault()
    setPage(1)
    loadProducts()
  }

  const handleAddToCart = (product) => {
    addToCart(product, 1)
    setAddedToCart(product.id)

    // Reset animation after 1 second
    setTimeout(() => {
      setAddedToCart(null)
    }, 1000)
  }

  if (loading && products.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <Package className="w-12 h-12 mx-auto mb-4 text-muted-foreground animate-pulse" />
          <p className="text-muted-foreground">Cargando productos...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Catálogo de Productos</h1>
          <p className="text-muted-foreground">Explora nuestros productos disponibles</p>
        </div>
      </div>

      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
          <Input
            type="text"
            placeholder="Buscar productos..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9"
          />
        </div>
        <Button type="submit">Buscar</Button>
      </form>

      {products.length === 0 ? (
        <div className="text-center py-12">
          <Package className="w-16 h-16 mx-auto mb-4 text-muted-foreground" />
          <h3 className="text-lg font-semibold mb-2">No se encontraron productos</h3>
          <p className="text-muted-foreground">Intenta con otros términos de búsqueda</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {products.map((product) => (
              <Card key={product.id} className="flex flex-col overflow-hidden">
                {product.image_url && (
                  <div className="aspect-square w-full overflow-hidden bg-gray-100">
                    <img
                      src={product.image_url}
                      alt={product.name}
                      className="h-full w-full object-cover object-center transition-transform hover:scale-105"
                      onError={(e) => {
                        e.target.style.display = 'none'
                      }}
                    />
                  </div>
                )}
                <CardHeader>
                  <CardTitle className="text-lg line-clamp-2">{product.name}</CardTitle>
                  <CardDescription className="line-clamp-3">
                    {product.description || 'Sin descripción disponible'}
                  </CardDescription>
                </CardHeader>
                <CardContent className="flex-1">
                  <div className="space-y-2">
                    <div className="flex items-baseline justify-between">
                      <span className="text-2xl font-bold text-primary">
                        ${product.price}
                      </span>
                    </div>
                    <div className="text-sm text-muted-foreground">
                      Stock: {product.stock > 0 ? (
                        <span className="text-green-600 font-semibold">{product.stock} unidades</span>
                      ) : (
                        <span className="text-red-600 font-semibold">Agotado</span>
                      )}
                    </div>
                  </div>
                </CardContent>
                <CardFooter>
                  <Button
                    onClick={() => handleAddToCart(product)}
                    disabled={product.stock <= 0}
                    className={`w-full transition-all duration-300 ${
                      addedToCart === product.id
                        ? 'bg-green-600 hover:bg-green-600 scale-95'
                        : ''
                    }`}
                  >
                    <ShoppingCart className={`w-4 h-4 mr-2 transition-transform duration-300 ${
                      addedToCart === product.id ? 'scale-125' : ''
                    }`} />
                    {addedToCart === product.id ? '¡Agregado!' : 'Agregar al carrito'}
                  </Button>
                </CardFooter>
              </Card>
            ))}
          </div>

          {totalPages > 1 && (
            <div className="flex justify-center gap-2 mt-8">
              <Button
                variant="outline"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
              >
                Anterior
              </Button>
              <span className="flex items-center px-4 text-sm text-muted-foreground">
                Página {page} de {totalPages}
              </span>
              <Button
                variant="outline"
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page === totalPages}
              >
                Siguiente
              </Button>
            </div>
          )}
        </>
      )}
    </div>
  )
}

