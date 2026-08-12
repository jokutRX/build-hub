const BASE_URL = 'http://127.0.0.1:8000/api/requests'

/**
 * Базовый HTTP-клиент для обработки запросов и ошибок
 * @param {string} endpoint - Относительный или абсолютный путь
 * @param {RequestInit} options - Параметры запроса fetch
 */
async function request(endpoint = '', options = {}) {
  const url = endpoint.startsWith('http') ? endpoint : `${BASE_URL}${endpoint}`
  
  const headers = {
    'Accept': 'application/json',
    ...options.headers,
  }

  // Если передаем тело запроса и не переопределили Content-Type, ставим JSON
  if (options.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json'
  }

  const config = {
    ...options,
    headers,
  }

  const response = await fetch(url, config)

  // Обработка 204 No Content
  if (response.status === 204) {
    return true
  }

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    // 1. Ошибки валидации Symfony (формат violations / errors)
    if (data?.errors && typeof data.errors === 'object') {
      const firstErrorField = Object.keys(data.errors)[0]
      if (firstErrorField && Array.isArray(data.errors[firstErrorField]) && data.errors[firstErrorField].length > 0) {
        throw new Error(data.errors[firstErrorField][0])
      }
    }

    // 2. Стандартное сообщение об ошибке
    const errorMessage = data?.detail || data?.message || data?.error || `Ошибка сервера (${response.status})`
    throw new Error(errorMessage)
  }

  return data
}

export const supplyApi = {
  /**
   * Получить список всех заявок
   * @param {string|null} [date] - Дата фильтрации (YYYY-MM-DD)
   */
  getAll(date = null) {
    const query = date ? `?date=${encodeURIComponent(date)}` : ''
    return request(query)
  },

  /**
   * Создать новую заявку
   * @param {Object} requestData
   */
  create(requestData) {
    return request('', {
      method: 'POST',
      body: JSON.stringify(requestData)
    })
  },

  /**
   * Массовое изменение статусов ("В доставку", "Завершить")
   * @param {number[]} ids
   * @param {string} status
   */
  updateBulkStatus(ids, status) {
    return request('/bulk-status', {
      method: 'POST',
      body: JSON.stringify({ ids, status })
    })
  },

  /**
   * Объединение нескольких заявок в один рейс
   * @param {number[]} ids
   */
  mergeToTrip(ids) {
    return request('/merge-trip', {
      method: 'POST',
      body: JSON.stringify({ ids })
    })
  },

  /**
   * Удалить заявку по ID
   * @param {number|string} id
   */
  delete(id) {
    return request(`/${id}`, {
      method: 'DELETE'
    })
  }
}