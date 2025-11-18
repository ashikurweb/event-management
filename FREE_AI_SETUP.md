# 🆓 Free AI Assistant Setup (Groq - 100% Free!)

## 🎉 Good News!

আপনি **Groq AI** ব্যবহার করতে পারেন যা **completely FREE**! Groq খুবই fast এবং free tier-এ unlimited requests নেই কিন্তু daily limit আছে যা সাধারণ use-এর জন্য যথেষ্ট।

## ✅ Groq Free Tier Benefits

- ✅ **100% Free** - কোনো payment method লাগবে না
- ✅ **Very Fast** - OpenAI-এর চেয়ে অনেক fast
- ✅ **Good Models** - Llama 3.1, Mixtral ইত্যাদি
- ✅ **Easy Setup** - শুধু API key লাগবে

## 📝 Step-by-Step Setup

### Step 1: Groq Account তৈরি করুন (Free)

1. **Groq Console-এ যান:**
   - Visit: https://console.groq.com/
   - "Sign Up" button-এ click করুন

2. **Account তৈরি করুন:**
   - Email address দিন
   - Password set করুন
   - Verify email করুন

3. **Login করুন**

### Step 2: API Key তৈরি করুন

1. **API Keys page-এ যান:**
   - https://console.groq.com/keys
   - অথবা Dashboard থেকে "API Keys" menu select করুন

2. **Create API Key:**
   - "Create API Key" button-এ click করুন
   - Key-এর জন্য একটি name দিন (যেমন: "EventHub AI")
   - "Create" button-এ click করুন

3. **Key Copy করুন:**
   - Key দেখতে পাবেন (একবারই দেখতে পাবেন!)
   - **Important**: এখনই copy করে রাখুন
   - Format হবে: `gsk_xxxxxxxxxxxxxxxxxxxxx`

### Step 3: .env File Update করুন

```bash
nano .env
```

নিচের line-গুলো add করুন:

```env
# Groq AI (FREE - Recommended)
GROQ_ENABLED=true
GROQ_API_KEY=gsk_YOUR_API_KEY_HERE

# OpenAI (Optional - keep if you want to use later)
OPENAI_API_KEY=sk-proj-7sn_QzTj5b_o...
```

**Important:**
- `GROQ_ENABLED=true` set করুন
- `GROQ_API_KEY=` এ আপনার Groq API key দিন
- Save করুন: `Ctrl+X`, তারপর `Y`, তারপর `Enter`

### Step 4: Cache Clear করুন

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 5: Test করুন

1. **Browser refresh করুন:** `Ctrl+Shift+R`
2. **Dashboard-এ যান**
3. **AI Assistant button-এ click করুন**
4. **Message পাঠান:** "Hello"
5. **এখন কাজ করবে!** ✅

## 🔍 Verify It's Working

```bash
php artisan tinker
>>> config('services.groq.enabled')
# Should return: true
>>> config('services.groq.api_key')
# Should return: gsk_...
```

## 🎯 Groq vs OpenAI

| Feature | Groq (Free) | OpenAI (Paid) |
|---------|-------------|---------------|
| Cost | **100% Free** | $0.001 per message |
| Speed | **Very Fast** | Fast |
| Models | Llama 3.1, Mixtral | GPT-3.5, GPT-4 |
| Setup | Easy | Easy |
| Payment | Not Required | Required |

## 📊 Groq Free Tier Limits

- **Daily Requests**: Generous limit (usually 10,000+ requests/day)
- **Rate Limit**: 30 requests/minute
- **Models Available**: 
  - `llama-3.1-8b-instant` (Fast, recommended)
  - `llama-3.1-70b-versatile` (More powerful)
  - `mixtral-8x7b-32768` (Good for long context)

## 🔧 Advanced Configuration

আপনি যদি অন্য model use করতে চান, `app/Services/AIAssistantService.php` file-এ model change করতে পারেন:

```php
// Line 23-24 এ
$this->model = 'llama-3.1-70b-versatile'; // More powerful
// বা
$this->model = 'mixtral-8x7b-32768'; // For longer context
```

## ⚠️ Troubleshooting

### যদি API Key Error দেখায়:

1. **Verify API Key:**
   ```bash
   php artisan tinker
   >>> config('services.groq.api_key')
   ```

2. **Check .env file:**
   ```bash
   grep GROQ .env
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

### যদি Rate Limit Error দেখায়:

- Groq free tier-এ rate limit আছে (30 requests/minute)
- কিছুক্ষণ অপেক্ষা করুন এবং আবার try করুন

### যদি Model Error দেখায়:

- Model name check করুন
- Default model: `llama-3.1-8b-instant` use করুন

## 🚀 Quick Setup Summary

1. ✅ https://console.groq.com/ - Account তৈরি করুন
2. ✅ https://console.groq.com/keys - API key তৈরি করুন
3. ✅ `.env` file-এ `GROQ_ENABLED=true` এবং `GROQ_API_KEY=` add করুন
4. ✅ `php artisan config:clear` run করুন
5. ✅ Browser refresh করুন এবং test করুন

## 💡 Tips

- Groq API key **completely free** - কোনো payment method লাগবে না
- Groq খুবই fast - OpenAI-এর চেয়ে অনেক দ্রুত response দেবে
- Daily limit আছে কিন্তু সাধারণ use-এর জন্য যথেষ্ট
- যদি limit exceed হয়, পরের দিন আবার reset হবে

## 🎉 Success!

এখন আপনার AI Assistant **100% free** Groq API use করবে! কোনো payment method বা credits add করার দরকার নেই!

---

**Setup completed? Test করুন এবং enjoy করুন!** 🚀

