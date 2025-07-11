# LiftingTracker Pro WordPress Theme
## v1.0.2
A modern, responsive WordPress theme for fitness tracking and workout management, built with JavaScript ES6+ modules, SCSS, and **Tailwind CSS** using `@wordpress/wp-scripts`.

## Features

- 🏋️‍♂️ **Workout Tracking**: Complete workout logging system
- 📊 **Progress Analytics**: Visual progress tracking and statistics
- 📱 **Mobile Responsive**: Optimized for all devices
- ⚡ **Fast Performance**: Modern build tools and optimization
- 🎨 **Modern Design**: Clean, professional interface with Tailwind CSS
- 🔧 **Customizable**: Easy to extend and modify
- 🎯 **Utility-First CSS**: Rapid development with Tailwind CSS framework

## Technology Stack

- **WordPress**: 6.0+
- **JavaScript**: ES6+ modules with WordPress APIs
- **CSS**: SCSS + Tailwind CSS v3.4+
- **Build Tools**: @wordpress/wp-scripts (webpack)
- **Plugins**: @tailwindcss/typography, @tailwindcss/forms, @tailwindcss/aspect-ratio

## Development Setup

### Prerequisites

- Node.js (v14 or higher)
- npm or yarn
- WordPress development environment

### Installation

1. **Clone or download the theme** to your WordPress themes directory:
   ```bash
   cd /wp-content/themes/
   ```

2. **Install dependencies**:
   ```bash
   npm install
   ```

3. **Run the setup script**:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

### Development Workflow

#### Build Commands

- **Development build with watch mode**:
  ```bash
  npm run start
  ```
  This starts the development server with hot reloading and file watching.

- **Production build**:
  ```bash
  npm run build
  ```
  Creates optimized, minified assets for production.

- **Linting**:
  ```bash
  npm run lint:js    # JavaScript linting
  npm run lint:css   # CSS/SCSS linting
  ```

- **Code formatting**:
  ```bash
  npm run format:js  # Format JavaScript files
  ```

#### File Structure

```
liftingtracker-pro/
├── src/                          # Source files
│   ├── js/                      # JavaScript modules
│   │   ├── main.js             # Main entry point
│   │   └── modules/            # Feature modules
│   │       ├── workout-tracker.js
│   │       ├── dashboard.js
│   │       ├── navigation.js
│   │       └── forms.js
│   └── scss/                   # SCSS stylesheets
│       ├── main.scss          # Main stylesheet (includes Tailwind)
│       ├── tailwind/         # Custom Tailwind components
│       │   └── components.scss
│       ├── abstracts/         # Variables, mixins, functions
│       ├── base/             # Reset, typography, base styles
│       ├── layout/           # Header, footer, grid
│       ├── components/       # Reusable components
│       ├── pages/           # Page-specific styles
│       └── utilities/       # Helper classes
├── build/                     # Compiled assets (auto-generated)
├── assets/                   # Static assets
├── includes/                 # PHP includes
├── templates/               # Template files
├── functions.php           # Theme functions
├── style.css              # Theme info (required by WordPress)
├── package.json          # Dependencies and scripts
├── webpack.config.js     # Webpack configuration
├── tailwind.config.js    # Tailwind CSS configuration
├── postcss.config.js     # PostCSS configuration
└── README.md            # This file
```

## Tailwind CSS Integration

### 🎨 **Utility-First CSS Framework**

The theme is built with Tailwind CSS v3.4+ for rapid development and consistent design:

#### **Custom Theme Configuration:**
- **Color Palette**: Custom primary, secondary, accent, success, warning, error colors
- **Typography**: Roboto font family with custom font sizes
- **Spacing**: Extended spacing scale for fitness app needs
- **Shadows**: Custom shadows for workout/exercise cards
- **Animations**: Custom animations (fadeIn, slideIn, etc.)

#### **Available Component Classes:**
- **Buttons**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline`, `.fab`
- **Forms**: `.form-input`, `.form-textarea`, `.form-select`, `.form-label`
- **Cards**: `.card`, `.card-workout`, `.card-exercise`
- **Modals**: `.modal`, `.modal-content`, `.modal-header`
- **Tables**: `.table`, `.table-header`, `.table-cell`
- **Alerts**: `.alert`, `.alert-success`, `.alert-warning`, `.alert-error`
- **Badges**: `.badge`, `.badge-primary`, `.badge-secondary`

#### **Usage Examples:**
```html
<!-- Button Examples -->
<button class="btn btn-primary">Save Workout</button>
<button class="btn btn-outline btn-sm">Cancel</button>

<!-- Form Examples -->
<div class="form-group">
    <label class="form-label">Exercise Name</label>
    <input type="text" class="form-input" placeholder="Enter exercise name">
</div>

<!-- Card Examples -->
<div class="card-workout">
    <h3 class="text-lg font-semibold mb-2">Today's Workout</h3>
    <p class="text-gray-600">Chest and Triceps</p>
</div>

<!-- Utility Classes -->
<div class="bg-primary-50 text-primary-800 p-4 rounded-lg">
    <h2 class="text-xl font-bold mb-2">Progress Update</h2>
    <p class="text-sm">You've completed 15 workouts this month!</p>
</div>
```

#### **Tailwind Plugins Included:**
- `@tailwindcss/typography` - Beautiful typography defaults
- `@tailwindcss/forms` - Better form styling
- `@tailwindcss/aspect-ratio` - Aspect ratio utilities

For detailed Tailwind CSS setup information, see `TAILWIND_SETUP.md`.

## JavaScript Architecture

### Module System

The theme uses ES6+ modules with a clean, organized structure:

- **main.js**: Entry point that imports all modules and initializes the theme
- **modules/**: Feature-specific modules (workout-tracker, dashboard, etc.)
- **WordPress Integration**: Uses `@wordpress/scripts` for seamless WP integration

### Key Features

- **Modern JavaScript**: ES6+ syntax with Babel compilation
- **WordPress APIs**: Integration with WordPress REST API and `@wordpress/api-fetch`
- **Internationalization**: Built-in i18n support using `@wordpress/i18n`
- **Performance**: Code splitting and optimized bundling

### Example Usage

```javascript
// Import WordPress utilities
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

// Use WordPress REST API
const workouts = await apiFetch({
    path: '/wp/v2/workouts'
});
```

## SCSS Architecture

### Structure

The SCSS is organized following the 7-1 pattern:

1. **abstracts/**: Variables, mixins, functions
2. **base/**: Reset, typography, base styles
3. **layout/**: Header, footer, grid systems
4. **components/**: Reusable UI components
5. **pages/**: Page-specific styles
6. **utilities/**: Helper classes and utilities

### Key Features

- **CSS Custom Properties**: Modern CSS variables
- **Responsive Design**: Mobile-first approach
- **Accessibility**: WCAG compliant styles
- **Performance**: Optimized output with PostCSS

### Example SCSS

```scss
// Using theme mixins
.workout-card {
    @include card;
    
    &:hover {
        transform: translateY(-2px);
        box-shadow: $shadow-lg;
    }
    
    @include mobile {
        margin-bottom: $spacing-md;
    }
}
```

## WordPress Integration

### Custom Post Types

The theme registers custom post types for:
- Workouts
- Exercises
- Workout Plans
- Progress Records

### REST API Endpoints

Custom endpoints for:
- `/wp-json/liftingtracker/v1/stats`
- `/wp-json/liftingtracker/v1/workouts`
- `/wp-json/liftingtracker/v1/exercises`

### Theme Support

The theme includes support for:
- Post thumbnails
- Custom logo
- Navigation menus
- HTML5 markup
- Title tag

## Customization

### Adding New JavaScript Modules

1. Create a new file in `src/js/modules/`
2. Import it in `src/js/main.js`
3. Initialize in the `initializeTheme()` function

### Adding New SCSS Components

1. Create a new file in `src/scss/components/`
2. Import it in `src/scss/main.scss`
3. Use theme variables and mixins

### Customizing Colors

Edit `src/scss/abstracts/_variables.scss`:

```scss
$primary-color: #your-color;
$secondary-color: #your-secondary-color;
```

## Performance Optimization

### Build Optimization

- **Code Splitting**: Automatic code splitting for optimal loading
- **Tree Shaking**: Dead code elimination
- **Minification**: CSS and JS minification in production
- **Asset Optimization**: Image and font optimization

### WordPress Optimization

- **Conditional Loading**: Scripts load only when needed
- **Caching**: Browser caching headers
- **Lazy Loading**: Images and components load on demand

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Contributing

1. Fork the repository
2. Create a feature branch
3. Follow the coding standards
4. Run tests and linting
5. Submit a pull request

## License

This theme is licensed under the GPL v2 or later.

## Support

For support and documentation, visit: [Your support URL]

---

**Happy coding! 🚀**
