/**
 * LiftingTracker Pro Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Dashboard functionality
    initWorkoutForm();
    initProgressCharts();
    initQuickActions();
});

function initWorkoutForm() {
    const workoutForm = document.getElementById('workout-form');
    if (!workoutForm) return;

    workoutForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const formData = new FormData(workoutForm);
        const submitButton = workoutForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        // Disable submit button
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';
        
        try {
            // Collect workout data
            const workoutData = {
                title: formData.get('workout_title'),
                date: formData.get('workout_date'),
                duration: formData.get('workout_duration'),
                calories: formData.get('calories_burned'),
                notes: formData.get('workout_notes'),
                exercises: collectExerciseData()
            };
            
            // Send to server
            const ajaxData = new FormData();
            ajaxData.append('action', 'liftingtracker_save_workout');
            ajaxData.append('workout_data', JSON.stringify(workoutData));
            ajaxData.append('nonce', liftingtracker_dashboard.nonce);
            
            const response = await fetch(liftingtracker_dashboard.ajax_url, {
                method: 'POST',
                body: ajaxData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showMessage('Workout saved successfully!', 'success');
                workoutForm.reset();
                // Optionally redirect to dashboard
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 2000);
            } else {
                showMessage(result.data.message || 'Failed to save workout', 'error');
            }
            
        } catch (error) {
            showMessage('An error occurred while saving the workout', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
}

function collectExerciseData() {
    const exercises = [];
    const exerciseRows = document.querySelectorAll('.exercise-row');
    
    exerciseRows.forEach(row => {
        const exercise = {
            name: row.querySelector('.exercise-name').value,
            sets: row.querySelector('.exercise-sets').value,
            reps: row.querySelector('.exercise-reps').value,
            weight: row.querySelector('.exercise-weight').value,
            notes: row.querySelector('.exercise-notes')?.value || ''
        };
        
        if (exercise.name) {
            exercises.push(exercise);
        }
    });
    
    return exercises;
}

function initProgressCharts() {
    // Weight Progress Chart
    const weightChartCtx = document.getElementById('weight-progress-chart');
    if (weightChartCtx && typeof Chart !== 'undefined') {
        loadProgressChart(weightChartCtx, 'weight');
    }
    
    // Volume Progress Chart
    const volumeChartCtx = document.getElementById('volume-progress-chart');
    if (volumeChartCtx && typeof Chart !== 'undefined') {
        loadProgressChart(volumeChartCtx, 'volume');
    }
}

async function loadProgressChart(ctx, type) {
    try {
        const formData = new FormData();
        formData.append('action', 'liftingtracker_get_progress_data');
        formData.append('exercise_name', 'Bench Press'); // Default exercise
        formData.append('period', 'month');
        formData.append('nonce', liftingtracker_dashboard.nonce);
        
        const response = await fetch(liftingtracker_dashboard.ajax_url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const data = result.data;
            const labels = data.map(item => item.date);
            const values = data.map(item => type === 'weight' ? item.weight : item.sets * item.reps * item.weight);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: type === 'weight' ? 'Weight (lbs)' : 'Volume (lbs)',
                        data: values,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Failed to load progress chart:', error);
    }
}

function initQuickActions() {
    // Add exercise row button
    const addExerciseBtn = document.getElementById('add-exercise-row');
    if (addExerciseBtn) {
        addExerciseBtn.addEventListener('click', function() {
            addExerciseRow();
        });
    }
    
    // Delete workout buttons
    document.querySelectorAll('.delete-workout').forEach(button => {
        button.addEventListener('click', async function(event) {
            event.preventDefault();
            
            if (!confirm('Are you sure you want to delete this workout?')) {
                return;
            }
            
            const workoutId = this.dataset.workoutId;
            await deleteWorkout(workoutId);
        });
    });
}

function addExerciseRow() {
    const container = document.getElementById('exercises-container');
    if (!container) return;
    
    const rowCount = container.children.length;
    const newRow = document.createElement('div');
    newRow.className = 'exercise-row grid grid-cols-5 gap-4 mb-4';
    newRow.innerHTML = `
        <input type="text" class="exercise-name" placeholder="Exercise name" 
               class="w-full p-2 border border-gray-300 rounded-md">
        <input type="number" class="exercise-sets" placeholder="Sets" min="1" 
               class="w-full p-2 border border-gray-300 rounded-md">
        <input type="number" class="exercise-reps" placeholder="Reps" min="1" 
               class="w-full p-2 border border-gray-300 rounded-md">
        <input type="number" class="exercise-weight" placeholder="Weight" min="0" step="0.5" 
               class="w-full p-2 border border-gray-300 rounded-md">
        <button type="button" class="remove-exercise bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600" 
                onclick="this.parentElement.remove()">Remove</button>
    `;
    
    container.appendChild(newRow);
}

async function deleteWorkout(workoutId) {
    try {
        const formData = new FormData();
        formData.append('action', 'liftingtracker_delete_workout');
        formData.append('workout_id', workoutId);
        formData.append('nonce', liftingtracker_dashboard.nonce);
        
        const response = await fetch(liftingtracker_dashboard.ajax_url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Workout deleted successfully', 'success');
            // Remove the workout element from the page
            const workoutElement = document.querySelector(`[data-workout-id="${workoutId}"]`);
            if (workoutElement) {
                workoutElement.remove();
            }
        } else {
            showMessage(result.data.message || 'Failed to delete workout', 'error');
        }
        
    } catch (error) {
        showMessage('An error occurred while deleting the workout', 'error');
    }
}

function showMessage(message, type = 'info') {
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    messageEl.textContent = message;
    
    // Add to page
    document.body.appendChild(messageEl);
    
    // Remove after 5 seconds
    setTimeout(() => {
        messageEl.remove();
    }, 5000);
}

// Utility functions
function formatDate(date) {
    return new Date(date).toLocaleDateString();
}

function formatWeight(weight) {
    return parseFloat(weight).toFixed(1) + ' lbs';
}
