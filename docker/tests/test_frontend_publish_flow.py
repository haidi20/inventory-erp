import os
import unittest


class TestFrontendPublishFlow(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        repo_root = os.path.dirname(base_dir)

        with open(os.path.join(base_dir, "docker-compose.yml"), "r", encoding="utf-8") as fh:
            cls.compose = fh.read()
        with open(os.path.join(base_dir, "nginx", "dev-prod.conf"), "r", encoding="utf-8") as fh:
            cls.nginx = fh.read()
        with open(os.path.join(base_dir, "run.bat"), "r", encoding="utf-8", errors="ignore") as fh:
            cls.run_bat = fh.read()
        with open(os.path.join(repo_root, "fe", "vite.config.js"), "r", encoding="utf-8") as fh:
            cls.vite_config = fh.read()
        with open(
            os.path.join(repo_root, "fe", "scripts", "docker_publish_frontend.js"),
            "r",
            encoding="utf-8",
        ) as fh:
            cls.publish_script = fh.read()

    def test_fe_services_publish_static_assets_instead_of_previewing(self):
        self.assertIn("/publish/dev", self.compose)
        self.assertIn("/publish/prod", self.compose)
        self.assertIn("node ./scripts/docker_publish_frontend.js dev /publish/dev", self.compose)
        self.assertIn("node ./scripts/docker_publish_frontend.js prod /publish/prod", self.compose)
        self.assertIn("if [ ! -x node_modules/.bin/vue-tsc ]; then npm ci --include=dev; fi", self.compose)
        self.assertIn("NPM_CONFIG_PRODUCTION=false", self.compose)
        self.assertNotIn("npm run preview", self.compose)
        self.assertNotIn("--port 3000", self.compose)
        self.assertNotIn("--port 8080", self.compose)

    def test_nginx_serves_frontends_from_static_publish_directory(self):
        self.assertIn("location = /dev/", self.nginx)
        self.assertIn("location = /prod/", self.nginx)
        self.assertIn("try_files /dev/current$uri /dev/current$uri/index.html /dev/current/index.html =404;", self.nginx)
        self.assertIn("try_files /prod/current$uri /prod/current$uri/index.html /prod/current/index.html =404;", self.nginx)
        self.assertNotIn("172.22.0.20:3000", self.nginx)
        self.assertNotIn("172.22.0.21:8080", self.nginx)

    def test_run_bat_refreshes_frontend_by_publish_script(self):
        self.assertIn("compose_local up -d --force-recreate", self.run_bat)
        self.assertIn("call :publish_frontend fe_dev dev /publish/dev", self.run_bat)
        self.assertIn("call :publish_frontend fe_prod prod /publish/prod", self.run_bat)
        self.assertIn(":publish_frontend", self.run_bat)
        self.assertIn(
            'call docker compose exec -T %PUBLISH_SERVICE% sh -lc "node ./scripts/docker_publish_frontend.js %PUBLISH_MODE% %PUBLISH_TARGET%"',
            self.run_bat,
        )
        self.assertIn(
            'docker compose exec -T %PUBLISH_SERVICE% sh -lc ""node ./scripts/docker_publish_frontend.js %PUBLISH_MODE% %PUBLISH_TARGET%""',
            self.run_bat,
        )
        self.assertNotIn('compose_local restart fe_prod', self.run_bat)
        self.assertNotIn('compose_local exec -T fe_dev sh -lc "npm run build:dev"', self.run_bat)

    def test_run_bat_builds_frontend_again_before_marking_it_running(self):
        dev_section_start = self.run_bat.find(":run_local_minimal")
        dev_section_end = self.run_bat.find(":run_be_dev")
        prod_section_start = self.run_bat.find(":run_fe_prod")
        prod_section_end = self.run_bat.find(":run_be_prod")

        dev_section = self.run_bat[dev_section_start:dev_section_end]
        prod_section = self.run_bat[prod_section_start:prod_section_end]

        dev_up = dev_section.find("compose_local up -d postgres_dev redis_dev be_dev fe_dev nginx")
        dev_publish = dev_section.find("call :publish_frontend fe_dev dev /publish/dev")
        dev_active = dev_section.find("echo FE dev aktif.")

        prod_up = prod_section.find("compose_local up -d postgres_prod redis_prod be_prod fe_prod nginx")
        prod_publish = prod_section.find("call :publish_frontend fe_prod prod /publish/prod")
        prod_active = prod_section.find("echo   FE prod : %LOCAL_FE_PROD_URL%")

        self.assertTrue(dev_up != -1 and dev_publish != -1 and dev_active != -1, "Flow FE dev belum lengkap di run.bat")
        self.assertTrue(prod_up != -1 and prod_publish != -1 and prod_active != -1, "Flow FE prod belum lengkap di run.bat")
        self.assertLess(dev_up, dev_publish, "FE dev harus menyalakan dependency dulu sebelum build/publish ulang")
        self.assertLess(dev_publish, dev_active, "FE dev tidak boleh ditandai aktif sebelum build/publish ulang selesai")
        self.assertLess(prod_up, prod_publish, "FE prod harus menyalakan dependency dulu sebelum build/publish ulang")
        self.assertLess(prod_publish, prod_active, "FE prod tidak boleh ditandai aktif sebelum build/publish ulang selesai")

    def test_vite_supports_custom_build_output_directory(self):
        self.assertIn("const runtimeEnv = process.env;", self.vite_config)
        self.assertIn("runtimeEnv.VITE_APP_BASE", self.vite_config)
        self.assertIn("BUILD_OUT_DIR", self.vite_config)
        self.assertIn("runtimeEnv.BUILD_OUT_DIR", self.vite_config)
        self.assertIn("outDir: buildOutDir", self.vite_config)

    def test_publish_script_updates_assets_before_index(self):
        self.assertIn("node_modules', '.bin', 'vue-tsc", self.publish_script)
        self.assertIn("npm', ['ci', '--include=dev']", self.publish_script)
        self.assertIn("function copyAllExceptIndex(sourceDir, targetDir)", self.publish_script)
        self.assertIn("if (entry === 'index.html')", self.publish_script)
        self.assertIn("cpSync(sourceIndex, join(finalDir, 'index.html')", self.publish_script)
        self.assertIn("Published ${mode} frontend to ${finalDir}", self.publish_script)


if __name__ == "__main__":
    unittest.main()
