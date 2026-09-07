<script setup>
import i18n from "../../i18n/hubspot";

// ============================================================
// PROPS
// ============================================================

defineProps({
  connected: {
    type: Boolean,
    default: false
  },

  account: {
    type: Object,
    default: null
  },

  overview: {
    type: Object,
    default: null
  },

  platformName: {
    type: String,
    default: "HubSpot Account"
  },

  loading: {
    type: Boolean,
    default: false
  },

  importing: {
    type: Boolean,
    default: false
  },

  error: {
    type: String,
    default: null
  }
});

// ============================================================
// EVENTS
// ============================================================

defineEmits([
  "connect",
  "disconnect",
  "import"
]);

// ============================================================
// LOCALE
// ============================================================

const locale = navigator.language?.startsWith("pt")
  ? "pt-BR"
  : "en-US";

// ============================================================
// TRANSLATION
// ============================================================

const t = (key) => {
  return i18n[locale]?.[key] || key;
};

// ============================================================
// REGION
// ============================================================

const regionLabel = (region) => {
  const map = {
    na1: {
      "pt-BR": "🌎 América",
      "en-US": "🌎 North America"
    },

    eu1: {
      "pt-BR": "🇪🇺 Europa",
      "en-US": "🇪🇺 Europe"
    },

    ap1: {
      "pt-BR": "🌏 Ásia",
      "en-US": "🌏 Asia"
    }
  };

  return map[region]?.[locale] || region || "—";
};

// ============================================================
// TIMEZONE
// ============================================================

const timezoneLabel = (timezone) => {
  if (!timezone) {
    return "—";
  }

  if (timezone.includes("Sao_Paulo")) {
    return locale === "pt-BR"
      ? "🇧🇷 Brasil (São Paulo)"
      : "🇧🇷 Brazil (São Paulo)";
  }

  if (timezone.includes("Eastern")) {
    return locale === "pt-BR"
      ? "🇺🇸 EUA (Eastern)"
      : "🇺🇸 USA (Eastern)";
  }

  return timezone;
};
</script>

<template>
  <div class="status-card">

    <!-- ================================================== -->
    <!-- LOADING -->
    <!-- ================================================== -->

    <div
      v-if="loading"
      class="status-center"
    >
      <div class="badge loading">
        ⏳ Verificando conexão com o HubSpot...
      </div>
    </div>

    <!-- ================================================== -->
    <!-- ERROR -->
    <!-- ================================================== -->

    <div
      v-else-if="error"
      class="status-center"
    >
      <div class="badge error">
        ⚠️ {{ error }}
      </div>

      <button
        v-if="!connected"
        class="btn primary"
        @click="$emit('connect')"
      >
        🔐 Conectar HubSpot
      </button>
    </div>

    <!-- ================================================== -->
    <!-- NÃO CONECTADO -->
    <!-- ================================================== -->

    <div
      v-else-if="!connected"
      class="status-center"
    >
      <div class="badge warning">
        ⚠️ HubSpot não conectado
      </div>

      <button
        class="btn primary"
        @click="$emit('connect')"
      >
        🔐 Conectar HubSpot
      </button>
    </div>

    <!-- ================================================== -->
    <!-- CONECTADO -->
    <!-- ================================================== -->

    <div v-else>

      <div class="status-header">

        <div class="badge success">
          ✅ {{ t("connected") }}
        </div>

        <div class="status-actions">

          <button
            class="btn secondary small"
            :disabled="importing"
            @click="$emit('import')"
          >
            <span v-if="!importing">
              📥 Importar contatos
            </span>

            <span v-else>
              ⏳ Importando...
            </span>
          </button>

          <button
            class="btn danger small"
            @click="$emit('disconnect')"
          >
            🔌 Desconectar
          </button>

        </div>
      </div>

      <!-- ================================================== -->
      <!-- ACCOUNT -->
      <!-- ================================================== -->

      <div class="account-box">

        <div>
          <span>Conta</span>

          <strong>
            {{
              overview?.company_name
              || account?.company_name
              || platformName
            }}
          </strong>
        </div>

        <div>
          <span>Portal ID</span>

          <strong>
            {{
              overview?.portal_id
              || account?.portal_id
              || "—"
            }}
          </strong>
        </div>

        <div>
          <span>Região</span>

          <strong>
            {{
              regionLabel(
                overview?.region
                || account?.region
              )
            }}
          </strong>
        </div>

        <div>
          <span>Timezone</span>

          <strong>
            {{
              timezoneLabel(
                overview?.timezone
                || account?.timezone
              )
            }}
          </strong>
        </div>

      </div>
    </div>

    <!-- ================================================== -->
    <!-- DOCUMENTATION -->
    <!-- ================================================== -->

    <a
      class="documentation-link"
      href="https://developers.hubspot.com/docs/api/overview"
      target="_blank"
      rel="noopener noreferrer"
    >
      📚 Documentação da API HubSpot
    </a>

  </div>
</template>

<style scoped>
.status-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
  margin-bottom: 24px;
}

/* ========================================================= */
/* CENTER */
/* ========================================================= */

.status-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

/* ========================================================= */
/* HEADER */
/* ========================================================= */

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.status-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ========================================================= */
/* ACCOUNT */
/* ========================================================= */

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

/* ========================================================= */
/* BUTTONS */
/* ========================================================= */

.btn {
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition:
    opacity 0.2s ease,
    background 0.2s ease;
}

.btn.small {
  padding: 8px 12px;
  font-size: 13px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn.primary {
  background: #ff7a59;
  color: white;
}

.btn.primary:hover:not(:disabled) {
  background: #ff5c35;
}

.btn.secondary {
  background: #33475b;
  color: white;
}

.btn.secondary:hover:not(:disabled) {
  background: #253342;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover:not(:disabled) {
  background: #b71c1c;
}

/* ========================================================= */
/* BADGES */
/* ========================================================= */

.badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
}

.badge.loading {
  background: #eff6ff;
  color: #1d4ed8;
}

.badge.warning {
  background: #fff7ed;
  color: #c2410c;
}

.badge.success {
  background: #ecfdf5;
  color: #047857;
}

.badge.error {
  background: #fef2f2;
  color: #b91c1c;
}

/* ========================================================= */
/* DOCUMENTATION */
/* ========================================================= */

.documentation-link {
  display: inline-block;
  margin-top: 12px;
  font-size: 12px;
  color: #64748b;
  text-decoration: none;
}

.documentation-link:hover {
  text-decoration: underline;
}

/* ========================================================= */
/* MOBILE */
/* ========================================================= */

@media (max-width: 768px) {
  .account-box {
    grid-template-columns: 1fr;
  }

  .status-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .status-actions {
    width: 100%;
    flex-wrap: wrap;
  }
}
</style>