import axios from 'axios'

const API_URL = 'http://localhost:8777/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Add token to requests if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Auth API
export const login = async (email, password) => {
  const response = await api.post('/login', { email, password })
  return response.data
}

// Products API
export const getProducts = async (params = {}) => {
  const response = await api.get('/products', { params })
  return response.data
}

// Orders API
export const createOrder = async (items) => {
  const response = await api.post('/orders', { items })
  return response.data
}

export const getOrderDetail = async (orderId) => {
  const response = await api.get(`/orders/${orderId}`)
  return response.data
}

export const checkoutOrder = async (orderId, paymentMethod = 'simulated') => {
  const response = await api.post(`/orders/${orderId}/checkout`, { paymentMethod })
  return response.data
}

export default api

