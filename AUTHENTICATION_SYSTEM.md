# WordPress Login and Registration System

## Overview

This document outlines the custom WordPress login and registration system for the LiftingTracker Pro theme. The system replaces the default WordPress login/registration pages with modern, multi-step forms inspired by the React components from the original application.

## Features

### Login System
- **Custom Login Template**: Modern, responsive login form
- **Email-based Authentication**: Uses email instead of username
- **Social Login Ready**: Placeholder for Google/social authentication
- **Custom Redirects**: Redirects to dashboard after successful login
- **Error Handling**: User-friendly error messages
- **Remember Me Functionality**: Persistent login sessions

### Registration System
- **Multi-step Registration**: 4-step process for comprehensive user onboarding
- **Session-based Progress**: Maintains progress between steps
- **Comprehensive User Profile**: Collects fitness-related information
- **Form Validation**: Client-side and server-side validation
- **Auto-login**: Automatically logs in users after registration
- **Custom User Meta**: Stores additional fitness data

## File Structure

```
├── login-template.php           # Custom login page template
├── registration-template.php    # Custom registration page template  
├── functions.php               # WordPress hooks and filters
└── src/scss/components/
    └── _auth-forms.scss        # Styling for login/registration forms
```

## WordPress Hooks and Filters

### URL Rewriting
- `login_url` - Redirects to custom login page
- `register_url` - Redirects to custom registration page
- Custom rewrite rules for `/login` and `/register` URLs

### Authentication Hooks
- `login_redirect` - Custom redirect after login
- `liftingtracker_registration_redirect` - Custom redirect after registration
- `liftingtracker_user_registered` - Fires after user registration

### User Management
- Custom user meta fields for fitness data
- `show_user_profile` - Adds fitness fields to user profile
- `edit_user_profile` - Adds fitness fields to user profile
- `personal_options_update` - Saves custom user meta
- `edit_user_profile_update` - Saves custom user meta

## Registration Steps

### Step 1: Account Creation
- **Fields**: Email, Password, Confirm Password
- **Validation**: Email format, password strength (8+ characters), password matching
- **Required**: Terms and conditions acceptance

### Step 2: Personal Information
- **Fields**: First Name, Last Name, Username, Date of Birth, Gender, Bio
- **Validation**: Username format (3-20 characters, alphanumeric + underscore)
- **Required**: First Name, Last Name, Username

### Step 3: Physical Attributes
- **Fields**: Height, Current Weight, Target Weight, Body Fat %, Preferred Units
- **Options**: Imperial (feet/inches, lbs) or Metric (cm, kg)
- **Validation**: All fields optional

### Step 4: Fitness Goals
- **Fields**: Fitness Level, Primary Goal, Workout Frequency, Macro Distribution
- **Validation**: Macro percentages must total 100%
- **Required**: Macro distribution validation

## Custom User Meta Fields

All fitness-related data is stored with the `liftingtracker_` prefix:

```php
// Physical attributes
'liftingtracker_height_feet'
'liftingtracker_height_inches'
'liftingtracker_height_cm'
'liftingtracker_current_weight'
'liftingtracker_target_weight'
'liftingtracker_body_fat_percentage'
'liftingtracker_preferred_units'

// Personal info
'liftingtracker_date_of_birth'
'liftingtracker_gender'

// Fitness goals
'liftingtracker_fitness_level'
'liftingtracker_primary_goal'
'liftingtracker_workout_frequency'
'liftingtracker_years_training'
'liftingtracker_activity_level'

// Nutrition
'liftingtracker_protein_percentage'
'liftingtracker_carbs_percentage'
'liftingtracker_fat_percentage'
'liftingtracker_dietary_restrictions'
'liftingtracker_allergies'
```

## Helper Functions

### `liftingtracker_get_user_fitness_data($user_id)`
Returns array of all fitness-related user meta data.

### `liftingtracker_user_has_complete_profile($user_id)`
Checks if user has completed required profile fields.

### `validate_current_step($step, $data)`
Validates form data for the current registration step.

### `get_step_validation_message($step, $data)`
Returns validation error message for the current step.

## Templates

### Login Template (`login-template.php`)
- Loads on `/login` URL
- Handles login form submission
- Includes social login placeholders
- Responsive design with error handling

### Registration Template (`registration-template.php`)
- Loads on `/register` URL
- Multi-step form with progress indicator
- Session-based data persistence
- Form validation and error handling

## Styling

### SCSS Structure
```scss
.auth-form {
  .auth-container {
    .auth-card {
      .auth-header
      .auth-form-inner {
        .form-group
        .checkbox-group
        .radio-group
        .alert
      }
      .social-login
      .auth-footer
    }
  }
}

.registration-form {
  .progress-indicator
  .step-content
  .form-navigation
}

.login-form {
  .login-options
}
```

### Key Features
- Responsive design (mobile-first)
- Modern UI with Tailwind-inspired styling
- Form validation states
- Loading states and animations
- Accessibility considerations

## Security Features

- **Nonce Verification**: All forms use WordPress nonces
- **Input Sanitization**: All user inputs are sanitized
- **Session Management**: Secure session handling for multi-step form
- **Password Requirements**: Minimum 8 characters
- **Email Validation**: Server-side email format validation

## Integration Points

### Navigation Menu
- Automatically adds login/logout links to primary menu
- Shows appropriate links based on authentication status

### Dashboard Integration
- Redirects to custom dashboard after login/registration
- User profile integration with fitness data

### Email System
- Custom welcome email after registration
- Disabled default WordPress user notifications
- Customizable email templates

## Customization

### Adding New Steps
1. Update `$total_steps` variable in registration template
2. Add new case in `renderStepContent()` switch statement
3. Add validation logic in `validate_current_step()`
4. Add new user meta fields if needed

### Modifying Fields
1. Update form HTML in appropriate step
2. Add validation rules
3. Update user meta saving logic
4. Add field to user profile display

### Styling Customization
- Modify `_auth-forms.scss` for visual changes
- Update CSS classes in template files
- Add custom animations or transitions

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile devices
- Progressive enhancement for older browsers
- Accessible form controls

## Dependencies

- WordPress 5.0+
- PHP 7.4+
- Modern browser with JavaScript enabled
- Session support (for multi-step registration)

## Testing

### Manual Testing Checklist
- [ ] Login form submission
- [ ] Registration form (all steps)
- [ ] Form validation (client and server)
- [ ] Session persistence between steps
- [ ] User creation and meta saving
- [ ] Email notifications
- [ ] Redirect functionality
- [ ] Mobile responsiveness
- [ ] Error handling

### Common Issues
- **Session not starting**: Check if PHP sessions are enabled
- **Form not submitting**: Verify nonce and form action
- **Validation errors**: Check JavaScript console for errors
- **Styling issues**: Ensure SCSS is compiled and imported

## Future Enhancements

- **Social Authentication**: Google, Facebook, Twitter integration
- **Two-factor Authentication**: SMS or email verification
- **Progressive Web App**: Offline registration capability
- **Advanced Validation**: Real-time username availability
- **File Uploads**: Profile pictures during registration
- **Email Verification**: Require email confirmation before activation
