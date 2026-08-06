const BASE_URL = 'http://localhost:8000/api/requests'

export const supplyApi = {
  async getAll() {
    const res = await fetch(BASE_URL)
    if (!res.ok) throw new Error('Не удалось загрузить заявки')
    return res.json()
  },

  async create(requestData) {
    const res = await fetch(BASE_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(requestData)
    })
    if (!res.ok) throw new Error('Ошибка при создании заявки')
    return res.json()
  },

  async delete(id) {
    const res = await fetch(`${BASE_URL}/${id}`, { 
      method: 'DELETE' 
    })
    if (!res.ok) throw new Error('Ошибка при удалении заявки')
  }
}