import type { User } from '@/types/user'
import apiClient from '@/services/api'

// Fetch a new CSRF token before every non-GET request
const getCsrfToken = async () => {
    await apiClient.get('/sanctum/csrf-cookie')
}

export async function fetchUsersApi(): Promise<User[]> {
    try {
      const response = await apiClient.get('/dms/users')
      return response.data
    } catch (error) {
      console.error('Error fetching users:', error)
      throw error
    }
}

export async function createUserApi(userData: Partial<User>): Promise<User> {
    try {
        await getCsrfToken()

        const formattedData = {
            fullName: userData.fullName,
            name: userData.name,
            email: userData.email,
            password: userData.password,
            division: userData.division,
            status: userData.status
        }

        const response = await apiClient.post('/dms/users', formattedData)
        return response.data.user
    } catch (error: any) {
        // Let the component handle the error
        throw error
    }
}

export async function updateUserApi(userId: string, updatedData: Partial<User>): Promise<User> {
    try {
        await getCsrfToken();

        const formattedData = {
            fullName: updatedData.fullName,
            name: updatedData.name,
            email: updatedData.email,
            division: updatedData.division,
            status: updatedData.status,
            password: updatedData.password || undefined
        };

        const response = await apiClient.put(`/dms/users/${userId}`, formattedData);
        return response.data.user;
    } catch (error: any) {
        // Add specific error handling for validation errors
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            if (errors.email) {
                throw {
                    message: 'Validation Error',
                    errors: {
                        email: ['This email address is already registered.']
                    }
                };
            }
            if (errors.name) {
                throw {
                    message: 'Validation Error',
                    errors: {
                        name: ['This username is already taken.']
                    }
                };
            }
            // Throw the original validation errors if not handled above
            throw {
                message: 'Validation Error',
                errors: errors
            };
        }
        // For other types of errors
        throw {
            message: error.response?.data?.message || 'Error updating user',
            type: 'error'
        };
    }
}

export async function deleteUserApi(userId: string): Promise<{ message: string; description: string; type: string }> {
    try {
        await getCsrfToken();
        const response = await apiClient.delete(`/dms/users/${userId}`);
        return response.data;
    } catch (error) {
        console.error('Error deleting user:', error);
        throw error;
    }
}

export async function resetPasswordApi(userId: string): Promise<{ message: string; description: string; type: string }> {
    try {
        await getCsrfToken();
        const response = await apiClient.post(`/dms/users/${userId}/reset-password`);
        return response.data;
    } catch (error) {
        console.error('Error resetting password:', error);
        throw error;
    }
}
