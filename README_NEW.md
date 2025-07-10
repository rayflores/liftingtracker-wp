# LiftingTracker Pro WordPress Theme

A modern, responsive WordPress theme for fitness tracking and workout management. Built with Tailwind CSS, modern JavaScript, and a complete asset pipeline.

## 🚀 Features

### **Modern Design**
- **Responsive layout** that works on all devices
- **Dark theme** with professional aesthetics
- **Material Design icons** for consistent UI
- **Tailwind CSS** for utility-first styling
- **Custom components** with hover effects and animations

### **Navigation System**
- **Responsive navigation** that adapts to screen size
- **Mobile-first approach** with collapsible menu
- **User authentication** integration
- **Smooth transitions** and animations
- **Accessibility features** with proper ARIA labels

### **Front Page Dashboard**
- **Hero section** with call-to-action buttons
- **Dashboard cards** showing workout progress
- **Weekly progress tracking** with visual indicators
- **Achievement system** with milestone tracking
- **Quick actions** for easy navigation

### **Technical Features**
- **Modern asset pipeline** with @wordpress/scripts
- **Tailwind CSS v3.4+** integration
- **SCSS architecture** with organized components
- **ES6+ JavaScript** with modular structure
- **Webpack** for asset bundling and optimization
- **PostCSS** for CSS processing
- **Build optimization** for production

## 📦 Installation

### **Requirements**
- WordPress 5.0+
- PHP 7.4+
- Node.js 16+
- npm or yarn

### **Setup**
1. Clone the repository:
   ```bash
   git clone https://github.com/rayflores/liftingtracker-wp.git
   cd liftingtracker-wp
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Build assets:
   ```bash
   npm run build
   ```

4. For development:
   ```bash
   npm run start
   ```

## 🛠️ Development

### **Available Scripts**
- `npm run build` - Build assets for production
- `npm run start` - Start development server with hot reload
- `npm run dev` - Build assets for development
- `npm run watch` - Watch files for changes

### **File Structure**
```
liftingtracker-wp/
├── build/                  # Compiled assets
├── src/
│   ├── js/
│   │   ├── main.js        # Main JavaScript entry
│   │   └── modules/       # JavaScript modules
│   └── scss/
│       ├── main.scss      # Main SCSS entry
│       ├── components/    # UI components
│       ├── pages/         # Page-specific styles
│       └── utilities/     # Utility classes
├── templates/             # PHP templates
├── includes/              # PHP includes
├── front-page.php         # Front page template
├── header.php             # Header template
├── footer.php             # Footer template
├── functions.php          # Theme functions
└── style.css             # Theme stylesheet
```

### **Key Components**
- **Navigation** - Responsive navbar with mobile menu
- **Dashboard Cards** - Workout tracking interface
- **Progress Tracking** - Visual progress indicators
- **User Authentication** - Login/logout functionality
- **Material Icons** - Consistent icon system

## 🎨 Customization

### **Colors**
The theme uses a custom color palette defined in `tailwind.config.js`:
- **Primary**: Blue tones for main actions
- **Secondary**: Gray tones for secondary elements
- **Accent**: Purple tones for highlights
- **Success**: Green tones for positive actions
- **Warning**: Yellow tones for alerts
- **Error**: Red tones for errors

### **Typography**
- **Font Family**: Inter (with fallbacks)
- **Font Weights**: 300, 400, 500, 600, 700, 800, 900
- **Responsive sizing** with clamp() functions

### **Breakpoints**
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: 1024px+
- **Large**: 1280px+

## 📱 Responsive Design

The theme is built with a mobile-first approach:
- **Flexible grid** system
- **Responsive images** and media
- **Touch-friendly** buttons and interactions
- **Optimized performance** on mobile devices

## 🔧 Configuration

### **WordPress Features**
- Custom logo support
- Custom menu locations
- Widget areas
- Post thumbnails
- HTML5 markup
- Title tag support

### **Tailwind Configuration**
Located in `tailwind.config.js` with:
- Custom color palette
- Extended spacing scale
- Custom font families
- Responsive breakpoints
- Plugin integrations

## 📋 Templates

### **Available Templates**
- `front-page.php` - Dashboard-style homepage
- `header.php` - Site header with navigation
- `footer.php` - Site footer
- `index.php` - Default template
- `dashboard-home.php` - User dashboard

### **Template Hierarchy**
Follows WordPress template hierarchy with custom templates for:
- Front page
- Dashboard pages
- User profiles
- Workout tracking

## 🧪 Testing

### **Browser Support**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### **Testing Commands**
```bash
# Build for production
npm run build

# Start development server
npm run start

# Watch for changes
npm run watch
```

## 🚀 Deployment

### **Production Build**
1. Run production build:
   ```bash
   npm run build
   ```

2. Upload theme to WordPress:
   - Upload entire theme folder to `/wp-content/themes/`
   - Activate in WordPress admin

### **Performance Optimization**
- Minified CSS and JavaScript
- Optimized images
- Efficient font loading
- Minimal HTTP requests

## 📖 Documentation

### **Additional Docs**
- [`NAVIGATION_UPDATES.md`](NAVIGATION_UPDATES.md) - Navigation system details
- [`FRONT_PAGE_TEMPLATE.md`](FRONT_PAGE_TEMPLATE.md) - Front page template guide
- [`TAILWIND_SETUP.md`](TAILWIND_SETUP.md) - Tailwind CSS setup guide

### **Code Comments**
All PHP, JavaScript, and SCSS files include comprehensive comments explaining:
- Function purposes
- Parameter descriptions
- Return values
- Usage examples

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Author

**Ray Flores**
- GitHub: [@rayflores](https://github.com/rayflores)
- Email: ray@rayflores.com

## 🙏 Acknowledgments

- **WordPress** - Content management system
- **Tailwind CSS** - Utility-first CSS framework
- **Material Design** - Icon system and design principles
- **@wordpress/scripts** - Build tooling

---

**Version**: 1.0.0  
**WordPress Compatibility**: 5.0+  
**PHP Compatibility**: 7.4+  
**License**: MIT
