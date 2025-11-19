# 🚀 AI Models Quick Setup Guide

## ⚡ Fastest Setup (Recommended)

### Option 1: OpenRouter (Easiest - Supports Both Models)

1. **Get API Key:**
   - Visit: https://openrouter.ai/
   - Sign up and add credits ($5 minimum)
   - Get your API key from dashboard

2. **Update `.env`:**
   ```env
   OPENROUTER_ENABLED=true
   OPENROUTER_API_KEY=sk-or-v1-your-api-key-here
   OPENROUTER_MODEL=qwen/qwen-2.5-coder-32b-instruct
   ```

3. **Done!** Your AI assistant will use Qwen 2.5 Coder.

---

### Option 2: Qwen 2.5 Coder Direct (Best for Coding)

1. **Get API Key:**
   - Option A: Use OpenRouter (recommended)
   - Option B: Use Hugging Face Inference API

2. **Update `.env`:**
   ```env
   QWEN_ENABLED=true
   QWEN_API_KEY=your-api-key-here
   QWEN_API_URL=https://openrouter.ai/api/v1/chat/completions
   QWEN_MODEL=qwen/qwen-2.5-coder-32b-instruct
   ```

---

### Option 3: DeepSeek R1 (Best for Reasoning)

1. **Get API Key:**
   - Visit: https://platform.deepseek.com/
   - Sign up and get API key

2. **Update `.env`:**
   ```env
   DEEPSEEK_ENABLED=true
   DEEPSEEK_API_KEY=sk-your-api-key-here
   DEEPSEEK_API_URL=https://api.deepseek.com/v1/chat/completions
   DEEPSEEK_MODEL=deepseek-r1-14b
   ```

---

## 📋 Priority Order

The system will use models in this order:

1. **Qwen 2.5 Coder** (if `QWEN_ENABLED=true`)
2. **DeepSeek R1** (if `DEEPSEEK_ENABLED=true`)
3. **OpenRouter** (if `OPENROUTER_ENABLED=true`)
4. **Groq** (if `GROQ_ENABLED=true`) - Free tier
5. **OpenAI** (fallback)

---

## 🎯 Recommended Configuration

For **Event Management System** with coding needs:

```env
# Primary: Qwen 2.5 Coder via OpenRouter
OPENROUTER_ENABLED=true
OPENROUTER_API_KEY=sk-or-v1-your-key
OPENROUTER_MODEL=qwen/qwen-2.5-coder-32b-instruct

# Secondary: DeepSeek R1 for complex reasoning (optional)
DEEPSEEK_ENABLED=false
DEEPSEEK_API_KEY=
```

---

## ✅ Test Your Setup

After configuring, test in your application:

1. Open AI Assistant in dashboard
2. Ask: "Create a Laravel migration for events table"
3. Should get complete migration code

---

## 💡 Tips

- **For coding tasks**: Use Qwen 2.5 Coder
- **For complex reasoning**: Use DeepSeek R1
- **For both**: Use OpenRouter and switch models via config
- **For free**: Keep Groq enabled as fallback

---

## 📚 Full Documentation

See `AI_MODELS_GUIDE.md` for detailed comparison and advanced configuration.

