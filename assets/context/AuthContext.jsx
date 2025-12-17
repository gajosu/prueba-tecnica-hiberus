/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useState, useEffect } from 'react'
import { login as loginApi } from '@/lib/api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check if user is logged in on mount
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')

    if (storedToken && storedUser) {
      setToken(storedToken)
      setUser(JSON.parse(storedUser))
    }
    setLoading(false)
  }, [])

  const login = async (email, password) => {
    try {
      const response = await loginApi(email, password)

      setToken(response.token)
      setUser({
        id: response.customer_id,
        email: response.email,
        name: response.name,
        role: response.role,
      })

      localStorage.setItem('token', response.token)
      localStorage.setItem('user', JSON.stringify({
        id: response.customer_id,
        email: response.email,
        name: response.name,
        role: response.role,
      }))

      return response
    } catch (error) {
      const message = error.response?.data?.message || error.response?.data?.error || 'Login failed'
      throw new Error(message)
    }
  }

  const logout = () => {
    setUser(null)
    setToken(null)
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  const isAdmin = () => user?.role === 'ROLE_ADMIN'
  const isAuthenticated = () => !!token

  return (
    <AuthContext.Provider value={{ user, token, login, logout, isAdmin, isAuthenticated, loading }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

