import type { E2EConfig } from 'e2e';
import { web } from '@e2e-dev/web';
import { createOpenRouter } from '@openrouter/ai-sdk-provider';

const openrouter = createOpenRouter({ apiKey: process.env.OPENROUTER_API_KEY ?? '' });

export default {
  // OpenRouter serves the model id and reads OPENROUTER_API_KEY.
  agents: {
    default: {
      model: openrouter('openai/gpt-4o-mini'),
      system: 'You are a thorough QA agent. Verify every outcome.',
    },
  },
  targets: [{
    engine: web(),
    app: {
      url: process.env.APP_URL ?? 'http://localhost:8000',
      // Or let the runner start the dev server:
      // command: { executable: 'npm', args: ['run', 'dev'] },
    },
  }],
  credentials: {
    superadmin: {
      username: process.env.E2E_SUPERADMIN_USERNAME ?? 'superadmin',
      password: process.env.E2E_SUPERADMIN_PASSWORD ?? 'superadmin',
    },
  },
} satisfies E2EConfig;
