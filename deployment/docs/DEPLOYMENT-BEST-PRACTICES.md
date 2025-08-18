# أفضل ممارسات النشر - Laravel Deployment Best Practices

## 🚀 أفضل طرق رفع النسخ بعد التعديل

### 1. استخدام Git مع GitHub Actions (الأفضل)
```bash
# بعد التعديل
git add .
git commit -m "وصف التعديل"
git push origin main
# سيتم النشر تلقائياً عبر GitHub Actions
```

### 2. النشر المباشر باستخدام rsync
```bash
# للنشر السريع
./deployment/scripts/deploy-files-only.sh
```

### 3. النشر الكامل مع النسخ الاحتياطي
```bash
# للنشر مع النسخ الاحتياطي
./deployment/deploy-master.sh deploy production
```

## 📁 الملفات التي لا ترفع في نسخة الإنتاج

### ملفات التطوير والاختبار
```
.git/                    # مجلد Git
.github/                 # إعدادات GitHub
node_modules/            # مكتبات Node.js
vendor/                  # مكتبات PHP (يتم تثبيتها على الخادم)
tests/                   # ملفات الاختبار
.phpunit.cache/          # ذاكرة PHPUnit
coverage/                # تقارير التغطية
```

### ملفات البيئة والإعدادات
```
.env                     # ملف البيئة المحلي
.env.local               # إعدادات محلية
.env.testing             # إعدادات الاختبار
*.log                    # ملفات السجلات
.DS_Store                # ملفات macOS
Thumbs.db                # ملفات Windows
```

### ملفات التخزين المؤقت
```
storage/logs/            # سجلات التطبيق
storage/framework/cache/ # ذاكرة التخزين المؤقت
storage/framework/sessions/ # جلسات المستخدمين
storage/framework/views/ # عروض مخزنة مؤقتاً
storage/app/public/      # ملفات عامة مرفوعة
```

### ملفات التطوير الأخرى
```
deployment/              # مجلد النشر
backups/                 # النسخ الاحتياطية
.trae/                   # إعدادات IDE
stubs/                   # قوالب الكود
```

## ⚡ استراتيجيات النشر المحسنة

### 1. النشر التدريجي (Zero Downtime)
- استخدام symlinks للتبديل السريع
- الاحتفاظ بنسخ متعددة
- اختبار النسخة قبل التفعيل

### 2. النشر الآمن
```bash
# إنشاء نسخة احتياطية قبل النشر
cp -r /home/siteeblue/htdocs/siteeblue.so /home/siteeblue/backups/backup-$(date +%Y%m%d-%H%M%S)

# رفع الملفات الجديدة
rsync -avz --exclude-from=exclude.txt ./

# اختبار التطبيق
php artisan config:cache
php artisan route:cache
```

### 3. أتمتة العملية
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [ main ]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          rsync -avz --exclude-from=.deployignore \
            ./ ${{ secrets.SERVER_USER }}@${{ secrets.SERVER_HOST }}:${{ secrets.SERVER_PATH }}/
```

## 🔧 إعداد ملف .deployignore

```bash
# إنشاء ملف استبعاد للنشر
cat > .deployignore << 'EOF'
.git/
.github/
node_modules/
vendor/
tests/
.phpunit.cache/
coverage/
.env
.env.*
*.log
.DS_Store
Thumbs.db
storage/logs/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
storage/app/public/
deployment/
backups/
.trae/
stubs/
EOF
```

## 📋 قائمة مراجعة النشر

### قبل النشر
- [ ] اختبار الكود محلياً
- [ ] تشغيل الاختبارات الآلية
- [ ] مراجعة التغييرات
- [ ] إنشاء نسخة احتياطية

### أثناء النشر
- [ ] رفع الملفات المحدثة فقط
- [ ] تشغيل migrations إذا لزم الأمر
- [ ] تحديث dependencies
- [ ] إعادة بناء assets

### بعد النشر
- [ ] اختبار التطبيق
- [ ] مراجعة السجلات
- [ ] التأكد من عمل جميع الوظائف
- [ ] إشعار الفريق

## 🛠️ أدوات مساعدة

### سكريبت النشر السريع
```bash
#!/bin/bash
# quick-deploy.sh
echo "🚀 بدء النشر السريع..."
git add .
read -p "وصف التعديل: " commit_msg
git commit -m "$commit_msg"
git push origin main
echo "✅ تم النشر بنجاح!"
```

### مراقبة النشر
```bash
# مراقبة حالة الخادم
watch -n 5 'curl -s -o /dev/null -w "%{http_code}" http://158.101.234.203'
```

## 🔍 استكشاف الأخطاء

### مشاكل شائعة وحلولها
1. **خطأ في الصلاحيات**: `chmod -R 755 storage bootstrap/cache`
2. **مشاكل البيئة**: التأكد من ملف `.env`
3. **مشاكل قاعدة البيانات**: `php artisan migrate:status`
4. **مشاكل الذاكرة المؤقتة**: `php artisan optimize:clear`

### سجلات مهمة
```bash
# سجلات Laravel
tail -f storage/logs/laravel.log

# سجلات الخادم
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```