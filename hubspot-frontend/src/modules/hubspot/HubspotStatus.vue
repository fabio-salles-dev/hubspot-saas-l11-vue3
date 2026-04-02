<script setup>
import i18n from '../../i18n/hubspot' // Importa o arquivo de tradução

defineProps({
  connected: Boolean,
  account: Object,
  overview: Object,
  platformName: String,
})

defineEmits(["connect", "disconnect"])

// 🌎 Detecta idioma (normalizado)
const locale = navigator.language?.startsWith('pt')
  ? 'pt-BR'
  : 'en-US'

// 🌍 Tradução
const t = (key) => {
  return i18n[locale]?.[key] || key
}

// 🌎 Região amigável
const regionLabel = (region) => {
  const map = {
    na1: {
      'pt-BR': '🌎 América',
      'en-US': '🌎 North America',
    },
    eu1: {
      'pt-BR': '🇪🇺 Europa',
      'en-US': '🇪🇺 Europe',
    },
    ap1: {
      'pt-BR': '🌏 Ásia',
      'en-US': '🌏 Asia',
    },
  }

  return map[region]?.[locale] || region || 'N/A'
}

// ⏰ Timezone amigável
const timezoneLabel = (tz) => {
  if (!tz) return '—'

  if (tz.includes('Sao_Paulo')) {
    return locale === 'pt-BR'
      ? '🇧🇷 Brasil (São Paulo)'
      : '🇧🇷 Brazil (São Paulo)'
  }

  if (tz.includes('Eastern')) {
    return locale === 'pt-BR'
      ? '🇺🇸 EUA (Eastern)'
      : '🇺🇸 USA (Eastern)'
  }

  return tz
}
</script>

<template>
  <div class="status-card">
    <!-- NÃO CONECTADO -->
    <div v-if="!connected" class="status-center">
      <div class="badge success">✅ {{ t('connected') }}</div>
      <button class="btn primary" @click="$emit('connect')">
        🔐 Conectar HubSpot
      </button>
    </div>

    <!-- CONECTADO -->
    <div v-else>
      <div class="status-header">
        <div class="badge success">✅ HubSpot conectado</div>
        <button class="btn danger small" @click="$emit('disconnect')">
          🔌 Desconectar
        </button>
      </div>

      <div class="account-box">
        <div>
          <span>Conta</span>
          <strong>{{ overview?.company_name || platformName }}</strong>
        </div>

        <div>
          <span>Portal ID</span>
          <strong>{{ overview?.portal_id }}</strong>
        </div>

        <div>
          <span>Região</span>

          <strong>
            {{ regionLabel(overview?.region) }}
          </strong>
        </div>

        <div>
          <span>Timezone</span>
          <strong>{{ timezoneLabel(overview?.timezone) }}</strong>
        </div>
      </div>
    </div>
    <a
      href="https://developers.hubspot.com/docs/api/overview"
      target="_blank"
      style="
        font-size: 12px;
        color: #64748b;
        margin-top: 12px;
        display: inline-block;
      "
    >
      📚 Documentação da API HubSpot
    </a>
  </div>

  <!-- Debug para você ver os dados chegando -->
  <!-- <pre v-if="connected" style="font-size: 10px; margin-top: 10px;">{{ account }}</pre> -->
</template>

<style scoped>
.status-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
  margin-bottom: 24px;
}

/* NÃO CONECTADO */
.status-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

/* HEADER CONECTADO */
.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

/* DADOS DA CONTA */
.account-box {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: #f8fafc;
  padding: 16px;
  border-radius: 12px;
}

.account-box div {
  display: flex;
  flex-direction: column;
  font-size: 14px;
}

.account-box span {
  color: #64748b;
  font-size: 12px;
}

.account-box strong {
  color: #0f172a;
  font-weight: 600;
}

/* BOTÕES */
.btn {
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn.small {
  padding: 8px 12px;
  font-size: 13px;
}

.btn.primary {
  background: #ff7a59;
  color: white;
}

.btn.primary:hover {
  background: #ff5c35;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover {
  background: #b71c1c;
}

/* BADGES */
.badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
}

.badge.warning {
  background: #fff7ed;
  color: #c2410c;
}

.badge.success {
  background: #ecfdf5;
  color: #047857;
}

/* MOBILE */
@media (max-width: 768px) {
  .account-box {
    grid-template-columns: 1fr;
  }

  .status-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>
