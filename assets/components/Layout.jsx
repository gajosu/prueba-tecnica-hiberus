import { useNavigate, useLocation, Link } from 'react-router-dom'
import { useAuth } from '@/context/AuthContext'
import { useCart } from '@/context/CartContext'
import { Button } from '@/components/ui/Button'
import { ShoppingCart, LogOut, Package, User } from 'lucide-react'

export default function Layout({ children }) {
  const { user, logout, isAdmin } = useAuth()
  const { getTotalItems } = useCart()
  const navigate = useNavigate()
  const location = useLocation()

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  const isActive = (path) => {
    return location.pathname === path
  }

  return (
    <div className="min-h-screen bg-background">
      <nav className="sticky top-0 z-50 border-b bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/60 shadow-sm">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-8">
              <Link to="/catalog" className="flex items-center gap-2 font-bold text-xl">
                <Package className="w-6 h-6" />
                <span>Sistema de Pedidos</span>
              </Link>

              <div className="hidden md:flex items-center gap-4">
                <Link to="/catalog">
                  <Button
                    variant={isActive('/catalog') ? 'default' : 'ghost'}
                    size="sm"
                  >
                    Catálogo
                  </Button>
                </Link>
                <Link to="/cart">
                  <Button
                    variant={isActive('/cart') ? 'default' : 'ghost'}
                    size="sm"
                    className="relative"
                  >
                    <ShoppingCart className="w-4 h-4 mr-2" />
                    Carrito
                    {getTotalItems() > 0 && (
                      <span className="absolute -top-1 -right-1 bg-destructive text-destructive-foreground text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse">
                        {getTotalItems()}
                      </span>
                    )}
                  </Button>
                </Link>
                {isAdmin() && (
                  <Link to="/admin/products">
                    <Button
                      variant={isActive('/admin/products') ? 'default' : 'ghost'}
                      size="sm"
                    >
                      Admin
                    </Button>
                  </Link>
                )}
              </div>
            </div>

            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2 text-sm">
                <User className="w-4 h-4" />
                <div className="hidden md:block">
                  <p className="font-semibold">{user?.name}</p>
                  <p className="text-xs text-muted-foreground">
                    {isAdmin() ? 'Administrador' : 'Usuario'}
                  </p>
                </div>
              </div>

              <Button
                variant="ghost"
                size="sm"
                onClick={handleLogout}
              >
                <LogOut className="w-4 h-4 mr-2" />
                Salir
              </Button>
            </div>
          </div>
        </div>
      </nav>

      <main className="container mx-auto px-4 py-8 pt-8">
        {children}
      </main>

      <footer className="border-t mt-auto">
        <div className="container mx-auto px-4 py-6 text-center text-sm text-muted-foreground">
          <p>Sistema de Gestión de Pedidos © 2025</p>
        </div>
      </footer>
    </div>
  )
}

