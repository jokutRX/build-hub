/**
 * Склонение существительных по числительным
 * @param {number} number - Число
 * @param {Array<string>} titles - Массив из 3 форм [1, 2-4, 5-0]
 * Пример: ['позиция', 'позиции', 'позиций'] или ['тонна', 'тонны', 'тонн']
 */
export const pluralize = (number, titles) => {
  const abs = Math.abs(number)
  const cases = [2, 0, 1, 1, 1, 2]
  return titles[
    abs % 100 > 4 && abs % 100 < 20
      ? 2
      : cases[abs % 10 < 5 ? abs % 10 : 5]
  ]
}

/**
 * Форматирование даты в ДД.ММ.ГГГГ
 */
export const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return dateString

  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()

  return `${day}.${month}.${year}`
}

/**
 * Склонение единиц измерения
 */
export const formatUnit = (count, unit) => {
  const normalizedUnit = (unit || '').toLowerCase().trim()

  const unitMap = {
    'тонна': ['тонна', 'тонны', 'тонн'],
    'тонны': ['тонна', 'тонны', 'тонн'],
    'т': ['тонна', 'тонны', 'тонн'],
    'штука': ['штука', 'штуки', 'штук'],
    'шт': ['штука', 'штуки', 'штук'],
    'м³': ['м³', 'м³', 'м³'],
    'куб': ['кубометр', 'кубометра', 'кубометров'],
    'м²': ['м²', 'м²', 'м²'],
    'кг': ['килограмм', 'килограмма', 'килограммов']
  }

  const forms = unitMap[normalizedUnit]
  if (!forms) return `${count} ${unit}` // Если единица измерения неизвестна, возвращаем как есть

  return `${count} ${pluralize(count, forms)}`
}