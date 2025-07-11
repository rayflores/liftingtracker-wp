/**
 * Workout Tracker Module
 *
 * Handles all workout tracking functionality including:
 * - Exercise selection
 * - Set tracking
 * - Progress recording
 * - Workout completion
 */

import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

class WorkoutTracker {
  constructor() {
    this.currentWorkout = null;
    this.exercises = [];
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadWorkoutTemplates();
  }

  bindEvents() {
    // Start workout button
    document.addEventListener("click", (e) => {
      if (e.target.matches(".start-workout")) {
        this.startWorkout();
      }
    });

    // Add exercise button
    document.addEventListener("click", (e) => {
      if (e.target.matches(".add-exercise")) {
        this.addExercise();
      }
    });

    // Complete set button
    document.addEventListener("click", (e) => {
      if (e.target.matches(".complete-set")) {
        this.completeSet(e.target);
      }
    });

    // Complete workout button
    document.addEventListener("click", (e) => {
      if (e.target.matches(".complete-workout")) {
        this.completeWorkout();
      }
    });
  }

  async loadWorkoutTemplates() {
    try {
      const templates = await apiFetch({
        path: "/wp/v2/workout-templates",
      });
      this.renderWorkoutTemplates(templates);
    } catch (error) {
      console.log(
        "Workout templates endpoint not available yet:",
        error.message
      );
      // If no templates are available, show a default message or skip this step
      this.renderWorkoutTemplates([]);
    }
  }

  renderWorkoutTemplates(templates) {
    const container = document.querySelector(".workout-templates");
    if (!container) return;

    if (templates.length === 0) {
      container.innerHTML = `
        <div class="no-templates">
          <p>${__(
            "No workout templates available yet.",
            "liftingtracker-pro"
          )}</p>
          <button class="btn btn-primary start-workout">
            ${__("Start Custom Workout", "liftingtracker-pro")}
          </button>
        </div>
      `;
      return;
    }

    const html = templates
      .map(
        (template) => `
            <div class="workout-template" data-template-id="${template.id}">
                <h3>${template.title.rendered}</h3>
                <p>${template.excerpt.rendered}</p>
                <button class="btn btn-primary start-workout" data-template-id="${
                  template.id
                }">
                    ${__("Start Workout", "liftingtracker-pro")}
                </button>
            </div>
        `
      )
      .join("");

    container.innerHTML = html;
  }

  startWorkout(templateId = null) {
    this.currentWorkout = {
      id: Date.now(),
      templateId,
      startTime: new Date(),
      exercises: [],
      isActive: true,
    };

    this.renderWorkoutInterface();
    this.saveWorkoutToStorage();
  }

  addExercise() {
    const exerciseSelect = document.querySelector("#exercise-select");
    const selectedExercise = exerciseSelect.value;

    if (!selectedExercise) return;

    const exercise = {
      id: Date.now(),
      name: selectedExercise,
      sets: [],
    };

    this.currentWorkout.exercises.push(exercise);
    this.renderWorkoutInterface();
    this.saveWorkoutToStorage();
  }

  completeSet(button) {
    const exerciseId = button.getAttribute("data-exercise-id");
    const weight = button.parentElement.querySelector(".weight-input").value;
    const reps = button.parentElement.querySelector(".reps-input").value;

    if (!weight || !reps) return;

    const exercise = this.currentWorkout.exercises.find(
      (ex) => ex.id == exerciseId
    );
    if (exercise) {
      exercise.sets.push({
        weight: parseFloat(weight),
        reps: parseInt(reps),
        completedAt: new Date(),
      });

      this.renderWorkoutInterface();
      this.saveWorkoutToStorage();
    }
  }

  async completeWorkout() {
    if (!this.currentWorkout) return;

    this.currentWorkout.completedAt = new Date();
    this.currentWorkout.isActive = false;

    try {
      await this.saveWorkoutToDatabase();
      this.clearWorkoutStorage();
      this.showWorkoutSummary();
    } catch (error) {
      console.error("Error saving workout:", error);
    }
  }

  renderWorkoutInterface() {
    const container = document.querySelector(".workout-interface");
    if (!container) return;

    const html = `
            <div class="workout-header">
                <h2>${__("Active Workout", "liftingtracker-pro")}</h2>
                <p>${__(
                  "Started:",
                  "liftingtracker-pro"
                )} ${this.currentWorkout.startTime.toLocaleTimeString()}</p>
            </div>
            
            <div class="exercise-selection">
                <select id="exercise-select">
                    <option value="">${__(
                      "Select Exercise",
                      "liftingtracker-pro"
                    )}</option>
                    <option value="bench-press">${__(
                      "Bench Press",
                      "liftingtracker-pro"
                    )}</option>
                    <option value="squat">${__(
                      "Squat",
                      "liftingtracker-pro"
                    )}</option>
                    <option value="deadlift">${__(
                      "Deadlift",
                      "liftingtracker-pro"
                    )}</option>
                </select>
                <button class="btn btn-secondary add-exercise">
                    ${__("Add Exercise", "liftingtracker-pro")}
                </button>
            </div>

            <div class="exercises-list">
                ${this.currentWorkout.exercises
                  .map((exercise) => this.renderExercise(exercise))
                  .join("")}
            </div>

            <div class="workout-actions">
                <button class="btn btn-primary complete-workout">
                    ${__("Complete Workout", "liftingtracker-pro")}
                </button>
            </div>
        `;

    container.innerHTML = html;
  }

  renderExercise(exercise) {
    return `
            <div class="exercise" data-exercise-id="${exercise.id}">
                <h3>${exercise.name}</h3>
                
                <div class="sets-completed">
                    ${exercise.sets
                      .map(
                        (set, index) => `
                        <div class="set-completed">
                            ${__("Set", "liftingtracker-pro")} ${index + 1}: ${
                              set.weight
                            }kg × ${set.reps}
                        </div>
                    `
                      )
                      .join("")}
                </div>

                <div class="set-input">
                    <input type="number" class="weight-input" placeholder="${__(
                      "Weight (kg)",
                      "liftingtracker-pro"
                    )}" step="0.5">
                    <input type="number" class="reps-input" placeholder="${__(
                      "Reps",
                      "liftingtracker-pro"
                    )}" step="1">
                    <button class="btn btn-success complete-set" data-exercise-id="${
                      exercise.id
                    }">
                        ${__("Complete Set", "liftingtracker-pro")}
                    </button>
                </div>
            </div>
        `;
  }

  saveWorkoutToStorage() {
    localStorage.setItem(
      "liftingtracker_current_workout",
      JSON.stringify(this.currentWorkout)
    );
  }

  loadWorkoutFromStorage() {
    const saved = localStorage.getItem("liftingtracker_current_workout");
    if (saved) {
      this.currentWorkout = JSON.parse(saved);
      return true;
    }
    return false;
  }

  clearWorkoutStorage() {
    localStorage.removeItem("liftingtracker_current_workout");
  }

  async saveWorkoutToDatabase() {
    const workoutData = {
      title: `Workout - ${this.currentWorkout.startTime.toLocaleDateString()}`,
      content: JSON.stringify(this.currentWorkout),
      status: "publish",
      type: "workout",
    };

    return await apiFetch({
      path: "/wp/v2/workouts",
      method: "POST",
      data: workoutData,
    });
  }

  showWorkoutSummary() {
    const totalSets = this.currentWorkout.exercises.reduce(
      (total, ex) => total + ex.sets.length,
      0
    );
    const duration = Math.round(
      (this.currentWorkout.completedAt - this.currentWorkout.startTime) /
        1000 /
        60
    );

    alert(`${__("Workout completed!", "liftingtracker-pro")}
        ${__("Duration:", "liftingtracker-pro")} ${duration} ${__(
          "minutes",
          "liftingtracker-pro"
        )}
        ${__("Total sets:", "liftingtracker-pro")} ${totalSets}`);
  }
}

// Initialize workout tracker
const workoutTracker = new WorkoutTracker();

// Check for active workout on page load
if (workoutTracker.loadWorkoutFromStorage()) {
  workoutTracker.renderWorkoutInterface();
}

export default WorkoutTracker;

