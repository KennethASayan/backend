import type { User } from '@/types/user'
import apiClient from '@/services/api'

export async function fetchUsersApi(): Promise<User[]> {
  try {
    const response = await apiClient.get('/dms/users')
    return response.data
  } catch (error) {
    console.error('Error fetching users:', error)
    throw error
  }
}

export async function createUserApi(userData: Omit<User, 'id'>): Promise<User> {
  try {
    // Get CSRF cookie first
    await apiClient.get('/sanctum/csrf-cookie')

    // Add a small delay after getting the CSRF cookie
    await new Promise(resolve => setTimeout(resolve, 300));

    const formattedData = {
      fullName: userData.fullName,
      name: userData.name,
      email: userData.email,
      division: userData.division,
      status: userData.status === 'Activated' ? 1 : 0,
      password: 'windows7'
    }

    const response = await apiClient.post('/dms/user', formattedData)
    return response.data.user
  } catch (error) {
    console.error('Error creating user:', error)
    throw error
  }
}

export async function updateUserApi(userId: string, updatedData: Partial<User>): Promise<User> {
  try {
    // Add this line to get the CSRF cookie before making the PUT request.
    await apiClient.get('/sanctum/csrf-cookie')
    const formattedData = {
      fullName: updatedData.fullName,
      name: updatedData.name,
      email: updatedData.email,
      division: updatedData.division,
      status: updatedData.status === 'Activated' ? 1 : 0
    }
    const response = await apiClient.put(`/dms/users/${userId}`, formattedData)
    return response.data.user
  } catch (error) {
    console.error('Error updating user:', error)
    throw error
  }
}

export async function deleteUserApi(userId: string): Promise<void> {
  try {
    // Add this line to get the CSRF cookie before making the DELETE request.
    await apiClient.get('/sanctum/csrf-cookie');
    await apiClient.delete(`/dms/users/${userId}`)
  } catch (error) {
    console.error('Error deleting user:', error)
    throw error
  }
}

export async function resetPasswordApi(userId: string): Promise<void> {
  try {
    // Add this line to get the CSRF cookie before making the POST request.
    await apiClient.get('/sanctum/csrf-cookie');
    await apiClient.post(`/dms/users/${userId}/reset-password`)
  } catch (error) {
    console.error('Error resetting password:', error)
    throw error
  }
}
