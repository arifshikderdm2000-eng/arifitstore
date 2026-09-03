import express from "express";
import { spawn, ChildProcess } from "child_process";
import { createProxyMiddleware } from "http-proxy-middleware";
import path from "path";

const PORT = 3000;
const PHP_PORT = 8888;
let phpProcess: ChildProcess | null = null;

function startPhpServer(): void {
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
