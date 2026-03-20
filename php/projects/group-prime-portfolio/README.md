# Group Prime Portfolio

A modern, responsive single-page portfolio web application showcasing Group Prime's projects and team members.

## 🚀 Features

- **Responsive Design**: Fully responsive layout that works seamlessly on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with smooth animations and transitions
- **Interactive Navigation**: Sticky navigation bar with smooth scrolling between sections
- **Dynamic Content**: Projects and team sections with dynamic data rendering
- **Hero Section**: Eye-catching introduction with call-to-action buttons
- **Project Showcase**: Card-based layout displaying team projects with links to GitHub and live demos
- **Team Display**: Professional team member cards with social media links
- **Contact Footer**: Comprehensive footer with contact information and quick links

## 🛠️ Technologies Used

- **HTML5**: Semantic markup and structure
- **CSS3**: Modern styling with Flexbox and Grid layouts
- **JavaScript (ES6+)**: Interactive features and dynamic behavior
- **PHP**: Backend API for data management
- **Font Awesome**: Icon library
- **Google Fonts**: Typography (Inter font family)

## 📁 Project Structure

```
group-prime-portfolio/
├── index.html              # Main HTML file
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── script.js          # JavaScript functionality
├── api/
│   └── data.php           # PHP backend API
├── images/                # Image assets directory
└── README.md              # Project documentation
```

## 🎨 Design Features

- **Color Scheme**: Professional gradient with primary (#6366f1) and secondary (#22d3ee) colors
- **Typography**: Clean Inter font family for optimal readability
- **Animations**: Smooth hover effects, fade-in animations, and parallax scrolling
- **Responsive Breakpoints**: Mobile-first approach with breakpoints at 480px and 768px
- **Micro-interactions**: Button hover effects, card animations, and smooth transitions

## 🔧 Functionality

### Navigation
- Fixed/sticky navigation bar that changes appearance on scroll
- Mobile hamburger menu for smaller screens
- Smooth scrolling to different sections
- Active link highlighting based on scroll position

### Hero Section
- Gradient background with floating animation
- Typing effect for the main title
- Call-to-action buttons with hover effects
- Responsive grid layout

### Projects Section
- Dynamic project cards rendered from data
- Hover effects with scale and shadow transformations
- Links to GitHub repositories and live demos
- Responsive grid layout that adapts to screen size

### Team Section
- Team member cards with avatars (initials)
- Social media links with hover animations
- Professional layout with role information
- Responsive grid system

### Footer
- Multi-column layout with company information
- Social media links
- Contact information
- Quick navigation links
- Copyright notice

## 📱 Responsive Design

The application is fully responsive with optimized layouts for:

- **Desktop (1200px+)**: Full grid layouts with maximum content visibility
- **Tablet (768px-1199px)**: Adjusted grid columns and spacing
- **Mobile (320px-767px)**: Single column layout with hamburger menu

## 🚀 Getting Started

### Prerequisites
- A web server (Apache, Nginx, or PHP's built-in server)
- PHP 7.4 or higher (for API functionality)

### Installation

1. **Clone or download** the project files to your local machine
2. **Set up a local server**:
   ```bash
   # Using PHP's built-in server
   cd group-prime-portfolio
   php -S localhost:8000
   
   # Or use Apache/Nginx by pointing the document root to the project folder
   ```
3. **Access the application**:
   Open your browser and navigate to `http://localhost:8000`

### API Endpoints

The PHP backend provides the following endpoints:

- `GET /api/data.php/projects` - Retrieve all projects
  - Query parameters: `featured`, `limit`, `sort`
- `GET /api/data.php/team` - Retrieve all team members
  - Query parameters: `role`, `limit`
- `GET /api/data.php/stats` - Retrieve portfolio statistics

### Example API Usage

```javascript
// Fetch featured projects
fetch('/api/data.php/projects?featured=true&limit=3')
  .then(response => response.json())
  .then(data => console.log(data));

// Fetch team members by role
fetch('/api/data.php/team?role=developer')
  .then(response => response.json())
  .then(data => console.log(data));
```

## 🎯 Customization

### Adding New Projects

1. **Static Method**: Update the `projectsData` array in `js/script.js`
2. **Dynamic Method**: Use the PHP API to manage projects

### Adding Team Members

1. **Static Method**: Update the `teamData` array in `js/script.js`
2. **Dynamic Method**: Use the PHP API to manage team data

### Customizing Colors

Update the CSS variables in `css/style.css`:

```css
:root {
    --primary-color: #6366f1;    /* Change primary color */
    --secondary-color: #22d3ee;  /* Change secondary color */
    /* ... other variables */
}
```

## 🌟 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 📊 Performance

- Optimized CSS with efficient selectors
- Minified JavaScript with debounced scroll events
- Lazy loading ready structure
- Responsive images support
- Efficient DOM manipulation

## 🔒 Security

- Input validation in PHP backend
- XSS prevention
- CORS headers configuration
- SQL injection prevention (ready for database integration)

## 🚧 Future Enhancements

- [ ] Contact form with email functionality
- [ ] Blog section
- [ ] Admin panel for content management
- [ ] Database integration for dynamic content
- [ ] Search functionality
- [ ] Dark mode toggle
- [ ] Multi-language support
- [ ] Performance optimization with lazy loading
- [ ] PWA capabilities

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Team

Group Prime - A talented team of developers and designers creating innovative digital solutions.

---

**Built with ❤️ by Group Prime Team**
