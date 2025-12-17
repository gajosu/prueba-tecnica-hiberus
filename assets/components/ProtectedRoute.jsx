import { Navigate } from 'react-router-dom'
import { useAuth } from '@/context/AuthContext'

export default function ProtectedRoute({ children }) {
  const { isAuthenticated, loading } = useAuth()

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="w-12 h-12 mx-auto mb-4 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
          <p className="text-muted-foreground">Cargando...</p>
        </div>
      </div>
    )
  }

  if (!isAuthenticated()) {
    return <Navigate to="/login" replace />
  }

  return children
}

