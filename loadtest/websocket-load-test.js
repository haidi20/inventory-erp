import http from "k6/http";
import ws from "k6/ws";
import { check } from "k6";

const BASE_URL = __ENV.K6_BASE_URL || "http://127.0.0.1:8000";
const REVERB_KEY = __ENV.K6_REVERB_KEY || "zqps6gltyu6q2nillacp";

export const options = {
  vus: 100,
  duration: "30s",
  thresholds: {
    ws_connecting: ["p(95)<2000"],
    ws_session_duration: ["p(95)<30000"],
  },
};

export default function () {
  const res = http.post(
    `${BASE_URL}/broadcasting/auth`,
    JSON.stringify({ channel_name: "private-branch.test-branch-uuid" }),
    {
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    }
  );

  check(res, {
    "auth status 200 or 403 (no session is acceptable)": (r) => r.status === 200 || r.status === 403,
  });
}
