<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Quiz service implementing business logic
 * Extends BaseService for DRY principle
 */
class QuizService extends BaseService
{
    protected array $relationships = ['course', 'lecture', 'questions.answers'];
    protected array $searchFields = ['title', 'description'];
    protected array $sortFields = ['id', 'title', 'created_at', 'updated_at', 'time_limit', 'max_attempts', 'passing_score'];

    /**
     * Get model instance
     */
    protected function getModelInstance(): Model
    {
        return new Quiz();
    }

    /**
     * Get quizzes by course
     */
    public function getByCourse(int $courseId, bool $activeOnly = true): Collection
    {
        $cacheKey = $this->getCacheKey('course', [$courseId, $activeOnly]);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($courseId, $activeOnly) {
            $query = $this->model->where('course_id', $courseId)
                ->with($this->relationships);
            
            if ($activeOnly) {
                $query->active();
            }
            
            return $query->orderBy('created_at')->get();
        });
    }

    /**
     * Apply filters specific to Quiz model
     */
    protected function applyFilters($query, \Illuminate\Http\Request $request): void
    {
        // Apply parent filters (search, sorting, date range)
        parent::applyFilters($query, $request);
        
        // Course filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }
    }

    /**
     * Get quizzes by lecture
     */
    public function getByLecture(int $lectureId, bool $activeOnly = true): Collection
    {
        $cacheKey = $this->getCacheKey('lecture', [$lectureId, $activeOnly]);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($lectureId, $activeOnly) {
            $query = $this->model->where('lecture_id', $lectureId)
                ->with($this->relationships);
            
            if ($activeOnly) {
                $query->active();
            }
            
            return $query->orderBy('created_at')->get();
        });
    }

    /**
     * Get quiz with questions for taking
     */
    public function getQuizForTaking(int $quizId, int $userId): ?Quiz
    {
        $quiz = $this->findById($quizId, ['questions.answers']);
        
        if (!$quiz || !$quiz->is_active) {
            return null;
        }
        
        // Check if user can take this quiz
        if (!$this->canUserTakeQuiz($quiz, $userId)) {
            return null;
        }
        
        // Randomize questions if enabled
        if ($quiz->randomize_questions && $quiz->questions->isNotEmpty()) {
            $quiz->setRelation('questions', $quiz->questions->shuffle());
        }
        
        // Randomize answers if enabled
        if ($quiz->randomize_answers) {
            $quiz->questions->each(function ($question) {
                if ($question->answers->isNotEmpty()) {
                    $question->setRelation('answers', $question->answers->shuffle());
                }
            });
        }
        
        return $quiz;
    }

    /**
     * Check if user can take quiz
     */
    public function canUserTakeQuiz(Quiz $quiz, int $userId): bool
    {
        if ($quiz->max_attempts <= 0) {
            return true; // Unlimited attempts
        }
        
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->count();
        
        return $attemptCount < $quiz->max_attempts;
    }

    /**
     * Get user's quiz attempts
     */
    public function getUserAttempts(int $quizId, int $userId): Collection
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->with(['quiz', 'answers'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get user's best score for quiz
     */
    public function getUserBestScore(int $quizId, int $userId): ?float
    {
        return QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->whereNotNull('score')
            ->max('score');
    }

    /**
     * Get quiz statistics
     */
    public function getQuizStats(int $quizId): array
    {
        $cacheKey = $this->getCacheKey('quiz_stats', [$quizId]);
        
        return Cache::remember($cacheKey, 1800, function () use ($quizId) { // 30 minutes cache
            $quiz = $this->findById($quizId);
            
            if (!$quiz) {
                return [];
            }
            
            $attempts = QuizAttempt::where('quiz_id', $quizId);
            
            $stats = [
                'total_attempts' => $attempts->count(),
                'completed_attempts' => $attempts->whereNotNull('completed_at')->count(),
                'average_score' => $attempts->whereNotNull('score')->avg('score') ?? 0,
                'highest_score' => $attempts->whereNotNull('score')->max('score') ?? 0,
                'lowest_score' => $attempts->whereNotNull('score')->min('score') ?? 0,
                'pass_rate' => 0
            ];
            
            if ($stats['completed_attempts'] > 0 && $quiz->passing_score > 0) {
                $passedAttempts = $attempts->where('score', '>=', $quiz->passing_score)->count();
                $stats['pass_rate'] = ($passedAttempts / $stats['completed_attempts']) * 100;
            }
            
            return $stats;
        });
    }

    /**
     * Get popular quizzes
     */
    public function getPopularQuizzes(int $limit = 10): Collection
    {
        $cacheKey = $this->getCacheKey('popular', [$limit]);
        
        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            return $this->model->withCount('attempts')
                ->active()
                ->with(['course', 'lecture'])
                ->orderBy('attempts_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get recent quizzes
     */
    public function getRecentQuizzes(int $limit = 10): Collection
    {
        $cacheKey = $this->getCacheKey('recent', [$limit]);
        
        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            return $this->model->active()
                ->with(['course', 'lecture'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Duplicate quiz
     */
    public function duplicate(int $quizId, array $overrides = []): ?Quiz
    {
        $originalQuiz = $this->findById($quizId, ['questions.answers']);
        
        if (!$originalQuiz) {
            return null;
        }
        
        $quizData = $originalQuiz->toArray();
        unset($quizData['id'], $quizData['created_at'], $quizData['updated_at'], $quizData['deleted_at']);
        
        // Apply overrides
        $quizData = array_merge($quizData, $overrides);
        
        // Ensure unique title
        if (!isset($overrides['title'])) {
            $quizData['title'] = $originalQuiz->title . ' (Copy)';
        }
        
        $newQuiz = $this->create($quizData);
        
        // Duplicate questions and answers
        foreach ($originalQuiz->questions as $question) {
            $questionData = $question->toArray();
            unset($questionData['id'], $questionData['quiz_id'], $questionData['created_at'], $questionData['updated_at']);
            $questionData['quiz_id'] = $newQuiz->id;
            
            $newQuestion = $newQuiz->questions()->create($questionData);
            
            // Duplicate answers
            foreach ($question->answers as $answer) {
                $answerData = $answer->toArray();
                unset($answerData['id'], $answerData['question_id'], $answerData['created_at'], $answerData['updated_at']);
                $answerData['question_id'] = $newQuestion->id;
                
                $newQuestion->answers()->create($answerData);
            }
        }
        
        return $newQuiz->fresh($this->relationships);
    }

    /**
     * Get custom statistics
     */
    protected function getCustomStats(): array
    {
        return [
            'with_time_limit' => $this->model->where('time_limit', '>', 0)->count(),
            'unlimited_attempts' => $this->model->where('max_attempts', 0)->count(),
            'with_passing_score' => $this->model->where('passing_score', '>', 0)->count(),
            'randomized_questions' => $this->model->where('randomize_questions', true)->count(),
            'randomized_answers' => $this->model->where('randomize_answers', true)->count()
        ];
    }

    /**
     * Prepare data before saving
     */
    protected function prepareData(array $data): array
    {
        // Ensure numeric fields are properly cast
        if (isset($data['time_limit'])) {
            $data['time_limit'] = (int) $data['time_limit'];
        }
        
        if (isset($data['max_attempts'])) {
            $data['max_attempts'] = (int) $data['max_attempts'];
        }
        
        if (isset($data['passing_score'])) {
            $data['passing_score'] = (float) $data['passing_score'];
        }
        
        // Ensure boolean fields are properly cast
        $booleanFields = ['is_active', 'show_results', 'randomize_questions', 'randomize_answers'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (bool) $data[$field];
            }
        }
        
        return $data;
    }

    /**
     * Hook after creating quiz
     */
    protected function afterCreate(Model $quiz, array $data): void
    {
        // Clear related caches
        if (isset($data['course_id'])) {
            Cache::forget($this->getCacheKey('course', [$data['course_id'], true]));
            Cache::forget($this->getCacheKey('course', [$data['course_id'], false]));
        }
        
        if (isset($data['lecture_id'])) {
            Cache::forget($this->getCacheKey('lecture', [$data['lecture_id'], true]));
            Cache::forget($this->getCacheKey('lecture', [$data['lecture_id'], false]));
        }
    }

    /**
     * Hook after updating quiz
     */
    protected function afterUpdate(Model $quiz, array $data): void
    {
        // Clear quiz-specific caches
        Cache::forget($this->getCacheKey('quiz_stats', [$quiz->id]));
        
        // Clear related caches if course or lecture changed
        if (isset($data['course_id']) && $quiz->wasChanged('course_id')) {
            Cache::forget($this->getCacheKey('course', [$quiz->getOriginal('course_id'), true]));
            Cache::forget($this->getCacheKey('course', [$quiz->getOriginal('course_id'), false]));
            Cache::forget($this->getCacheKey('course', [$data['course_id'], true]));
            Cache::forget($this->getCacheKey('course', [$data['course_id'], false]));
        }
        
        if (isset($data['lecture_id']) && $quiz->wasChanged('lecture_id')) {
            Cache::forget($this->getCacheKey('lecture', [$quiz->getOriginal('lecture_id'), true]));
            Cache::forget($this->getCacheKey('lecture', [$quiz->getOriginal('lecture_id'), false]));
            Cache::forget($this->getCacheKey('lecture', [$data['lecture_id'], true]));
            Cache::forget($this->getCacheKey('lecture', [$data['lecture_id'], false]));
        }
    }
}