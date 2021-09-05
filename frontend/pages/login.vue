<template>
  <v-main>
    <v-container fluid fill-height class="loginOverlay">
      <v-layout flex align-center justify-center>
        <v-flex xs12 sm4>
          <v-card class="pt-6" elevation="2">
            <v-card-text class="text-center">
              <v-icon x-large>mdi-checkbox-blank-circle</v-icon>
            </v-card-text>
            <v-card-text class="text-center pb-0">
              <h5 class="text-h5">Вход</h5>
              <div class="text-subtitle-1">Добро пожаловать!</div>
            </v-card-text>
            <v-card-text class="pt-0">
              <v-form v-model="valid" ref="form" class="ma-8" lazy-validation @submit.prevent="submit">
                <v-text-field
                  label="Эл. почта"
                  v-model="email"
                  type="email"
                  :rules="emailRules"
                  required
                  outlined
                  prepend-inner-icon="mdi-email"
                  dense
                  @focus="clearError"
                ></v-text-field>
                <v-text-field
                  label="Пароль"
                  v-model="password"
                  min="8"
                  prepend-inner-icon="mdi-lock"
                  type="password"
                  :rules="passwordRules"
                  required
                  outlined
                  dense
                  @focus="clearError"
                ></v-text-field>
                <v-alert dense outlined type="error" v-if="error">
                  {{ error }}
                </v-alert>
                <v-layout class="mt-6">
                  <v-spacer />
                  <v-btn
                    type="submit"
                    :class="{
                      'blue darken-4 white--text': valid,
                      disabled: !valid,
                    }"
                    >Войти</v-btn
                  >
                </v-layout>
              </v-form>
            </v-card-text>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>
  </v-main>
</template>

<script>
export default {
  data() {
    return {
      error: null,
      valid: false,
      password: "",
      passwordRules: [(v) => !!v || "Password is required"],
      email: "",
      emailRules: [
        (v) => !!v || "E-mail is required",
        (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
      ],
    };
  },
  methods: {
    clearError() {
      this.error = null;
    },
    submit() {
      if (!this.$refs.form.validate()) {
        return;
      }

      this.$auth
        .loginWith("laravelJWT", {
          data: {
            email: this.email,
            password: this.password,
          },
        })
        .then(() => {
          this.$router.push("/");
        })
        .catch((e) => {
          const message = !e.response ? e.message : e.response.data.error;
          this.error = message;
        });
    },
    clear() {
      this.$refs.form.reset();
    },
  },
};
</script>