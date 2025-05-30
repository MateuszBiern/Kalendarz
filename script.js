document.addEventListener('DOMContentLoaded', function () {
	let selectedDate = null
	let editingIndex = null
	let data = {}

	const calendarEl = document.getElementById('calendar')
	const taskPanel = document.getElementById('task-panel')
	const taskList = document.getElementById('task-list')
	const selectedDateTitle = document.getElementById('selected-date')
	const form = document.getElementById('add-task-form')
	const overlay = document.getElementById('overlay')
	const closeBtn = document.getElementById('close-panel')
	const cancelBtn = document.getElementById('cancel-edit')

	const calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		locale: 'pl',
		buttonText: {
			today: 'Dzisiaj',
		},
		dateClick: function (info) {
			if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
				alert('Musisz być zalogowany, aby dodać zadanie.')
				return
			}
			selectedDate = info.dateStr
			openTaskPanel(selectedDate)
			refreshDayCells()
		},
		datesSet: function () {
			refreshDayCells()
		},
	})

	calendar.render()

	fetch('tasks.php')
		.then(res => res.json())
		.then(tasks => {
			tasks.forEach(task => {
				if (!data[task.date]) data[task.date] = []
				data[task.date].push({
					id: task.id, // Dodaj to!
					type: task.type,
					text: task.text,
					short: task.short,
					assignedTo: task.assigned_to,
					done: task.done == 1,
				})
			})
			refreshDayCells()
		})
		.catch(err => console.error('Błąd podczas pobierania zadań:', err))

	function showModal() {
		taskPanel.classList.remove('hidden')
		overlay.classList.remove('hidden')
	}

	function hideModal() {
		taskPanel.classList.add('hidden')
		overlay.classList.add('hidden')
		form.reset()
		editingIndex = null
	}

	closeBtn?.addEventListener('click', hideModal)
	overlay?.addEventListener('click', hideModal)
	cancelBtn?.addEventListener('click', hideModal)

	function openTaskPanel(dateStr) {
		selectedDate = dateStr
		selectedDateTitle.textContent = `Zadania na dzień ${dateStr}`
		renderTaskList()
		showModal()
	}
	function renderTaskList() {
		taskList.innerHTML = ''

		const entries = data[selectedDate] || []
		console.log('Wyświetlam zadania dla daty:', selectedDate)
		console.log(entries)

		entries.forEach((entry, index) => {
			const li = document.createElement('li')
			li.dataset.id = entry.id

			// Zawsze checkbox
			const checkbox = document.createElement('input')
			checkbox.type = 'checkbox'
			checkbox.checked = entry.done
			checkbox.addEventListener('change', () => {
				toggleDone(selectedDate, index)
			})
			li.appendChild(checkbox)

			// Typ zadania
			li.append(` [${entry.type === 'task' ? 'Zadanie' : 'Zrobione'}] `)

			// Pełny tekst zadania (jeśli istnieje), w przeciwnym razie skrót
			const displayText = entry.text || entry.short
			li.append(displayText)

			// Przypisanie osoby, jeśli jest
			if (entry.assignedTo) {
				li.append(` ➡ ${entry.assignedTo}`)
			}

			const editBtn = document.createElement('button')
			editBtn.textContent = 'Edytuj'
			editBtn.addEventListener('click', () => {
				editingIndex = index
				const entry = data[selectedDate][index]
				document.getElementById('entry-type').value = entry.type
				document.getElementById('entry-text').value = entry.text
				document.getElementById('entry-short').value = entry.short
				document.getElementById('assigned-to').value = entry.assignedTo || ''
				showModal()
			})
			li.appendChild(editBtn)

			const deleteBtn = document.createElement('button')
			deleteBtn.textContent = 'Usuń'
			deleteBtn.addEventListener('click', () => {
				deleteTask(selectedDate, index)
			})
			li.appendChild(deleteBtn)

			taskList.appendChild(li)
		})
	}

	form.addEventListener('submit', e => {
		e.preventDefault()

		if (typeof IS_LOGGED_IN !== 'undefined' && !IS_LOGGED_IN) {
			alert('Musisz być zalogowany, aby dodać zadanie.')
			return
		}

		const type = document.getElementById('entry-type').value
		const text = document.getElementById('entry-text').value
		const short = document.getElementById('entry-short').value
		const assignedTo = document.getElementById('assigned-to').value

		if (!data[selectedDate]) data[selectedDate] = []

		if (editingIndex !== null) {
			const originalTask = data[selectedDate][editingIndex]
			const updatedTask = {
				type,
				text,
				short,
				assignedTo: assignedTo || null,
				done: originalTask.done,
				id: originalTask.id, // Dodaj ID oryginalnego zadania
			}

			fetch('tasks.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					action: 'update',
					...updatedTask, // Teraz zawiera też id
				}),
			})
				.then(response => response.json())
				.then(result => {
					if (result.success) {
						data[selectedDate][editingIndex] = updatedTask
						renderTaskList()
						refreshDayCells()
						form.reset()
						editingIndex = null
						hideModal()
					} else {
						alert('Błąd podczas aktualizacji: ' + (result.error || 'Nieznany błąd'))
					}
				})
				.catch(err => alert('Błąd sieci: ' + err.message))
		} else {
			fetch('tasks.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					date: selectedDate,
					type,
					text,
					short,
					assignedTo,
					done: false,
				}),
			})
				.then(response => response.json())
				.then(result => {
					if (result.success) {
						data[selectedDate].push({
							id: result.id, // id zwrócone z backendu przy tworzeniu
							type,
							text,
							short,
							assignedTo: assignedTo || null,
							done: false,
						})
						renderTaskList()
						refreshDayCells()
						form.reset()
					} else {
						alert('Błąd podczas zapisu: ' + (result.error || 'Nieznany błąd'))
					}
				})
				.catch(err => alert('Błąd sieci: ' + err.message))
		}
	})

	function refreshDayCells() {
		document.querySelectorAll('.task-short').forEach(el => el.remove())

		Object.keys(data).forEach(dateKey => {
			const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateKey}"]`)
			if (!cell) return

			const dayData = data[dateKey]
			dayData.forEach(entry => {
				if (entry.short) {
					const shortDiv = document.createElement('div')
					shortDiv.className = 'task-short' + (entry.done ? ' done' : '')
					shortDiv.textContent = entry.short
					const frame = cell.querySelector('.fc-daygrid-day-frame')
					if (frame) frame.appendChild(shortDiv)
				}
			})
		})
	}

	function deleteTask(date, index) {
		const task = data[date][index]
		if (!task) return

		fetch('tasks.php', {
			method: 'DELETE',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ date, short: task.short }),
		})
			.then(res => {
				if (!res.ok) throw new Error('Błąd sieci: ' + res.status)
				return res.json()
			})
			.then(result => {
				console.log('Delete response:', result)
				if (result.success) {
					data[date].splice(index, 1)
					renderTaskList()
					refreshDayCells()
				} else {
					alert('Błąd usuwania zadania: ' + (result.error || 'Nieznany błąd'))
				}
			})
			.catch(err => alert('Błąd sieci: ' + err.message))
	}

	function toggleDone(date, index) {
		const task = data[date][index]
		if (!task) return

		task.done = !task.done

		fetch('tasks.php', {
			method: 'PUT',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				id: task.id,
				date: date,
				type: task.type,
				text: task.text,
				short: task.short,
				assignedTo: task.assignedTo,
				done: task.done,
			}),
		})
			.then(res => res.json())
			.then(result => {
				if (!result.success) {
					alert('Błąd zapisu statusu')
				}
				refreshDayCells()
			})
	}
})
