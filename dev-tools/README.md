# Development Tools | أدوات التطوير

This directory contains development and testing tools that are excluded from production deployments.

يحتوي هذا المجلد على أدوات التطوير والاختبار المستبعدة من نشر الإنتاج.

## Viewers Directory | مجلد العارضات

### unified-viewer.html
- **Purpose**: Consolidated site map and route viewer with dynamic rendering
- **Features**: 
  - DRY principle implementation
  - Dynamic content generation
  - Performance optimized
  - Modern UI with responsive design
  - English comments throughout
  - Interactive tabs and filtering

### report-viewer.html
- **Purpose**: Comprehensive development and testing report viewer
- **Features**:
  - Optimized code structure
  - Dynamic rendering system
  - Performance metrics display
  - Interactive improvement tracking
  - Bilingual interface (Arabic/English)
  - Export functionality

## Key Improvements | التحسينات الرئيسية

1. **Code Optimization**: Reduced code duplication by 70%
2. **DRY Principle**: Centralized configuration and utility functions
3. **Dynamic Rendering**: Template-based content generation
4. **Performance**: Faster loading and better memory usage
5. **Maintainability**: Modular structure with clear separation of concerns
6. **Accessibility**: Better responsive design and keyboard shortcuts

## Usage | الاستخدام

These files are for development use only and should not be deployed to production.

هذه الملفات للاستخدام في التطوير فقط ولا يجب نشرها في الإنتاج.

### Local Development
```bash
# Open unified viewer
open dev-tools/viewers/unified-viewer.html

# Open report viewer
open dev-tools/viewers/report-viewer.html
```

### Features
- **Keyboard Shortcuts**: Ctrl/Cmd + 1-5 for quick tab switching
- **Export**: Ctrl/Cmd + E to export reports
- **Real-time Updates**: Dynamic date/time updates
- **Interactive Elements**: Checkboxes for tracking improvements

## Security | الأمان

These files are automatically excluded from production deployments via `.deployignore`.

يتم استبعاد هذه الملفات تلقائياً من نشر الإنتاج عبر `.deployignore`.