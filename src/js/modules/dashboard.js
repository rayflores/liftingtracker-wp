/**
 * Dashboard Module
 *
 * Handles dashboard functionality including:
 * - Statistics display
 * - Recent workouts
 * - Progress charts
 * - Quick actions
 */

import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

class Dashboard {
  constructor() {
    this.stats = {};
    this.recentWorkouts = [];
    this.init();
  }

  init() {
    if (document.body.classList.contains("dashboard")) {
      this.loadDashboardData();
      this.bindEvents();
    }
  }

  bindEvents() {
    // Refresh stats button
    document.addEventListener("click", (e) => {
      if (e.target.matches(".refresh-stats")) {
        this.loadDashboardData();
      }
    });

    // Quick workout buttons
    document.addEventListener("click", (e) => {
      if (e.target.matches(".quick-workout")) {
        const workoutType = e.target.getAttribute("data-workout-type");
        this.startQuickWorkout(workoutType);
      }
    });
  }

  async loadDashboardData() {
    try {
      const [stats, workouts] = await Promise.all([
        this.loadStats(),
        this.loadRecentWorkouts(),
      ]);

      this.stats = stats;
      this.recentWorkouts = workouts;
      this.renderDashboard();
    } catch (error) {
      console.error("Error loading dashboard data:", error);
    }
  }

  async loadStats() {
    try {
      const response = await apiFetch({
        path: "/liftingtracker/v1/stats",
      });
      return response;
    } catch (error) {
      // Fallback to dummy data if API not available
      return {
        totalWorkouts: 0,
        totalSets: 0,
        totalWeight: 0,
        currentStreak: 0,
      };
    }
  }

  async loadRecentWorkouts() {
    try {
      const workouts = await apiFetch({
        path: "/wp/v2/workouts?per_page=5&orderby=date&order=desc",
      });
      return workouts;
    } catch (error) {
      console.error("Error loading recent workouts:", error);
      return [];
    }
  }

  renderDashboard() {
    this.renderStatsCards();
    this.renderRecentWorkouts();
    this.renderQuickActions();
  }

  renderStatsCards() {
    const container = document.querySelector(".stats-cards");
    if (!container) return;

    const html = `
            <div class="stat-card">
                <div class="stat-icon">🏋️</div>
                <div class="stat-content">
                    <h3>${this.stats.totalWorkouts || 0}</h3>
                    <p>${__("Total Workouts", "liftingtracker-pro")}</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💪</div>
                <div class="stat-content">
                    <h3>${this.stats.totalSets || 0}</h3>
                    <p>${__("Total Sets", "liftingtracker-pro")}</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚖️</div>
                <div class="stat-content">
                    <h3>${this.formatWeight(this.stats.totalWeight || 0)}</h3>
                    <p>${__("Total Weight", "liftingtracker-pro")}</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔥</div>
                <div class="stat-content">
                    <h3>${this.stats.currentStreak || 0}</h3>
                    <p>${__("Day Streak", "liftingtracker-pro")}</p>
                </div>
            </div>
        `;

    container.innerHTML = html;
  }

  renderRecentWorkouts() {
    const container = document.querySelector(".recent-workouts");
    if (!container) return;

    if (this.recentWorkouts.length === 0) {
      container.innerHTML = `
                <div class="no-workouts">
                    <p>${__(
                      "No workouts yet. Start your first workout!",
                      "liftingtracker-pro"
                    )}</p>
                </div>
            `;
      return;
    }

    const html = `
            <h3>${__("Recent Workouts", "liftingtracker-pro")}</h3>
            <div class="workouts-list">
                ${this.recentWorkouts
                  .map((workout) => this.renderWorkoutCard(workout))
                  .join("")}
            </div>
        `;

    container.innerHTML = html;
  }

  renderWorkoutCard(workout) {
    const workoutData = JSON.parse(workout.content.rendered || "{}");
    const date = new Date(workout.date).toLocaleDateString();
    const duration =
      workoutData.completedAt && workoutData.startTime
        ? Math.round(
            (new Date(workoutData.completedAt) -
              new Date(workoutData.startTime)) /
              1000 /
              60
          )
        : 0;

    return `
            <div class="workout-card">
                <div class="workout-header">
                    <h4>${workout.title.rendered}</h4>
                    <span class="workout-date">${date}</span>
                </div>
                <div class="workout-details">
                    <span class="workout-duration">${duration} ${__(
                      "min",
                      "liftingtracker-pro"
                    )}</span>
                    <span class="workout-exercises">${
                      workoutData.exercises?.length || 0
                    } ${__("exercises", "liftingtracker-pro")}</span>
                </div>
                <button class="btn btn-outline view-workout" data-workout-id="${
                  workout.id
                }">
                    ${__("View Details", "liftingtracker-pro")}
                </button>
            </div>
        `;
  }

  renderQuickActions() {
    const container = document.querySelector(".quick-actions");
    if (!container) return;

    const html = `
            <h3>${__("Quick Actions", "liftingtracker-pro")}</h3>
            <div class="quick-actions-grid">
                <button class="quick-action-btn quick-workout" data-workout-type="push">
                    <div class="action-icon">🏋️</div>
                    <span>${__("Push Workout", "liftingtracker-pro")}</span>
                </button>
                
                <button class="quick-action-btn quick-workout" data-workout-type="pull">
                    <div class="action-icon">🚣</div>
                    <span>${__("Pull Workout", "liftingtracker-pro")}</span>
                </button>
                
                <button class="quick-action-btn quick-workout" data-workout-type="legs">
                    <div class="action-icon">🦵</div>
                    <span>${__("Leg Workout", "liftingtracker-pro")}</span>
                </button>
                
                <button class="quick-action-btn" onclick="window.location.href='${
                  window.location.origin
                }/workout-tracker'">
                    <div class="action-icon">➕</div>
                    <span>${__("Custom Workout", "liftingtracker-pro")}</span>
                </button>
            </div>
        `;

    container.innerHTML = html;
  }

  startQuickWorkout(workoutType) {
    // Redirect to workout tracker with pre-selected template
    window.location.href = `${window.location.origin}/workout-tracker?template=${workoutType}`;
  }

  formatWeight(weight) {
    if (weight >= 1000) {
      return `${(weight / 1000).toFixed(1)}k kg`;
    }
    return `${weight} kg`;
  }
}

// Initialize dashboard
const dashboard = new Dashboard();

export default Dashboard;

