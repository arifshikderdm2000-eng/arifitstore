import express from "express";
import { spawn, execSync, ChildProcess } from "child_process";
import { createProxyMiddleware } from "http-proxy-middleware";
import path from "path";

const PORT = 3000;
const PHP_PORT = 8888;
let phpProcess: ChildProcess | null = null;
let isShuttingDown = false;

function ensurePhpInstalled(): void {
  try {
    execSync("which php", { stdio: "ignore" });
  } catch {
    console.log("PHP binary not found. Installing PHP packages non-interactively...");
    try {
      execSync(
        'DEBIAN_FRONTEND=noninteractive apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" php-cli php-sqlite3 php-mbstring php-curl php-mysql',
        { stdio: "inherit" }
      );
      console.log("PHP installed successfully!");
    } catch (installErr) {
      console.error("Failed to automatically install PHP:", installErr);
    }
  }
}

function startPhpServer(): void {
  if (isShuttingDown) return;

  ensurePhpInstalled();

  try {
    console.log(`Starting PHP built-in server on port ${PHP_PORT}...`);
    phpProcess = spawn("php", ["-S", `127.0.0.1:${PHP_PORT}`, "router.php"], {
      cwd: process.cwd(),
      stdio: "inherit",
    });

    phpProcess.on("error", (err) => {
      console.error("Failed to start PHP server:", err);
    });

    phpProcess.on("exit", (code, signal) => {
      console.log(`PHP server exited with code ${code}, signal ${signal}`);
      phpProcess = null;
      if (!isShuttingDown) {
        console.log("Attempting to restart PHP server in 2 seconds...");
        setTimeout(startPhpServer, 2000);
      }
    });
  } catch (err) {
    console.error("Error spawning PHP process:", err);
  }
}

async function startServer() {
  startPhpServer();

  const app = express();

  // Health check endpoint
  app.get("/api/health", (_req, res) => {
    res.json({
      status: "ok",
      server: "PHP+Express Reverse Proxy",
      currency: "BDT (৳)",
      target: "InfinityFree Ready",
    });
  });

  // Proxy everything else to PHP server
  app.use(
    "/",
    createProxyMiddleware({
      target: `http://127.0.0.1:${PHP_PORT}`,
      changeOrigin: true,
      ws: false,
    })
  );

  const server = app.listen(PORT, "0.0.0.0", () => {
    console.log(`Arif Shikder IT Services server running on port ${PORT}`);
    console.log(`Proxying requests to PHP server at http://127.0.0.1:${PHP_PORT}`);
  });

  const cleanup = () => {
    console.log("Shutting down servers...");
    isShuttingDown = true;
    if (phpProcess) {
      phpProcess.kill();
      phpProcess = null;
    }
    server.close(() => {
      process.exit(0);
    });
  };

  process.on("SIGINT", cleanup);
  process.on("SIGTERM", cleanup);
}

startServer();
