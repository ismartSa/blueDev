// Common CSS classes for Player components
export const playerStyles = {
    // Container styles
    containers: {
        main: 'min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30',
        content: 'flex flex-col lg:flex-row gap-6 p-4 lg:p-6 max-w-7xl mx-auto',
        videoSection: 'flex-1 space-y-4',
        sidebar: 'w-full lg:w-96 space-y-6'
    },
    
    // Card styles
    cards: {
        base: 'bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20',
        elevated: 'bg-white/90 backdrop-blur-sm rounded-xl shadow-xl border border-white/30',
        interactive: 'bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 hover:shadow-xl hover:bg-white/90 transition-all duration-300'
    },
    
    // Button styles
    buttons: {
        primary: 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300',
        secondary: 'bg-white/80 text-slate-700 px-4 py-2 rounded-lg border border-slate-200 hover:bg-white hover:shadow-md transition-all duration-200',
        ghost: 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 p-2 rounded-lg transition-colors',
        success: 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-4 py-2 rounded-lg font-medium shadow-md hover:shadow-lg transition-all duration-200',
        danger: 'bg-gradient-to-r from-red-500 to-pink-600 text-white px-4 py-2 rounded-lg font-medium shadow-md hover:shadow-lg transition-all duration-200'
    },
    
    // Text styles
    text: {
        heading: 'text-2xl font-bold text-slate-800',
        subheading: 'text-lg font-semibold text-slate-700',
        body: 'text-slate-600',
        muted: 'text-slate-500 text-sm',
        accent: 'text-indigo-600 font-medium'
    },
    
    // Progress styles
    progress: {
        container: 'w-full bg-slate-200 rounded-full h-2 overflow-hidden',
        bar: 'h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all duration-500 ease-out',
        text: 'text-xs font-medium text-slate-600 mt-1'
    },
    
    // Video player styles
    video: {
        container: 'relative bg-black rounded-xl overflow-hidden shadow-2xl',
        player: 'w-full aspect-video',
        overlay: 'absolute inset-0 bg-black/50 flex items-center justify-center',
        loading: 'text-white text-lg font-medium'
    },
    
    // Lecture item styles
    lecture: {
        item: 'flex items-center justify-between p-3 rounded-lg transition-all duration-200',
        current: 'bg-gradient-to-r from-indigo-50 to-purple-50 border-l-4 border-indigo-500',
        completed: 'bg-emerald-50 border-l-4 border-emerald-500',
        default: 'hover:bg-slate-50 border-l-4 border-transparent',
        title: 'font-medium text-slate-800',
        duration: 'text-sm text-slate-500'
    },
    
    // Section styles
    section: {
        header: 'flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-slate-100 rounded-t-lg border-b border-slate-200',
        title: 'font-semibold text-slate-800',
        content: 'p-2 space-y-1'
    },
    
    // Stats styles
    stats: {
        container: 'grid grid-cols-1 md:grid-cols-3 gap-4',
        card: 'p-4 rounded-lg text-center',
        primary: 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white',
        success: 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white',
        warning: 'bg-gradient-to-br from-amber-500 to-orange-600 text-white',
        value: 'text-2xl font-bold',
        label: 'text-sm opacity-90 mt-1'
    },
    
    // Animation classes
    animations: {
        fadeIn: 'animate-fade-in',
        slideUp: 'animate-slide-up',
        bounce: 'animate-bounce',
        pulse: 'animate-pulse',
        spin: 'animate-spin'
    },
    
    // State classes
    states: {
        loading: 'opacity-50 pointer-events-none',
        disabled: 'opacity-50 cursor-not-allowed',
        active: 'ring-2 ring-indigo-500 ring-offset-2',
        focus: 'focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none'
    }
}

// Dynamic class generators
export const getVariantClasses = (variant, type = 'button') => {
    const variants = {
        button: {
            primary: playerStyles.buttons.primary,
            secondary: playerStyles.buttons.secondary,
            ghost: playerStyles.buttons.ghost,
            success: playerStyles.buttons.success,
            danger: playerStyles.buttons.danger
        },
        card: {
            base: playerStyles.cards.base,
            elevated: playerStyles.cards.elevated,
            interactive: playerStyles.cards.interactive
        },
        stats: {
            primary: playerStyles.stats.primary,
            success: playerStyles.stats.success,
            warning: playerStyles.stats.warning
        }
    }
    
    return variants[type]?.[variant] || variants[type]?.base || ''
}

export const getLectureStateClasses = (isCompleted, isCurrent) => {
    if (isCurrent) return playerStyles.lecture.current
    if (isCompleted) return playerStyles.lecture.completed
    return playerStyles.lecture.default
}

export const getProgressBarClasses = (progress) => {
    const baseClasses = playerStyles.progress.bar
    if (progress >= 100) return `${baseClasses} bg-gradient-to-r from-emerald-500 to-teal-600`
    if (progress >= 75) return `${baseClasses} bg-gradient-to-r from-blue-500 to-indigo-600`
    if (progress >= 50) return `${baseClasses} bg-gradient-to-r from-yellow-500 to-orange-600`
    return baseClasses
}