const BASE_URL = 'http://127.0.0.1:8000/api/requests'

// Вспомогательная функция для парсинга и обработки ошибок API
async function handleResponse(response) {
  const data = await response.json().catch(() => null)

  if (!response.ok) {
    // 1. Ошибки валидации Symfony (формат violations / errors)
    if (data?.errors && typeof data.errors === 'object') {
      const firstErrorField = Object.keys(data.errors)[0]
      if (firstErrorField && data.errors[firstErrorField].length > 0) {
        throw new Error(data.errors[firstErrorField][0])
      }
    }

    // 2. Обычное сообщение об ошибке (detail, message или fallback)
    const errorMessage = data?.detail || data?.message || data?.error || `Ошибка сервера (${response.status})`
    throw new Error(errorMessage)
  }

  return data
}

export const supplyApi = {
  /**
   * Получить список всех заявок
   */
  async getAll() {
    const res = await fetch(BASE_URL, {
      headers: {
        'Accept': 'application/json'
      }
    })
    return handleResponse(res)
  },

  /**
   * Создать новую заявку
   */
  async create(requestData) {
    const res = await fetch(BASE_URL, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json' 
      },
      body: JSON.stringify(requestData)
    })
    
    return handleResponse(res)
  },

  /**
   * Удалить заявку по ID
   */
  async delete(id) {
    const res = await fetch(`${BASE_URL}/${id}`, { 
      method: 'DELETE',
      headers: { 
        'Accept': 'application/json' 
      }
    })
    
    // Если бэкенд возвращает 204 No Content
    if (res.status === 204) return true

    return handleResponse(res)
  }
}