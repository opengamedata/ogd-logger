# import libraries
import json
import logging
import requests
from datetime import datetime
from unittest import TestCase
# import 3rd-party libraries
# import ogd-core libraries.
from ogd.common.schemas.configs.TestConfigSchema import TestConfigSchema
from ogd.common.utils.Logger import Logger
# import locals
from tests.config.t_config import settings

class t_log_remote(TestCase):
    DEFAULT_ADDRESS = "127.0.0.1:5000"

    @classmethod
    def setUpClass(cls) -> None:
        """Set up the t_log_remote testbed class.

        In particular, we set the following class vars:
        - testing_config : A TestConfigSchema based on the settings in t_config.py
        - base_url : The base URL endpoint in the config.
        - headers : The headers to include in our testing requests.

        TODO : remove the https:// bit of the base URL when we start using TestRequest, which will auto-add it for us if not in the config.
        """
        cls.testing_config = TestConfigSchema.FromDict(name="HelloAPITestConfig", all_elements=settings, logger=None)
        cls.base_url = f"https://{cls.testing_config.NonStandardElements.get('REMOTE_ADDRESS', t_log_remote.DEFAULT_ADDRESS)}"
        cls.headers = {
            "Accept": "*/*",
            "Accept-Language": "en-US,en;q=0.5",
            "Accept-Encoding": "gzip, deflate, br, zstd",
            "Content-Type": "application/x-www-form-urlencoded",
            "DNT": "1",
            "Connection": "keep-alive",
            "Sec-Fetch-Dest": "empty",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Site": "cross-site"
        }

        _level = logging.DEBUG if cls.testing_config.Verbose else logging.INFO
        Logger.InitializeLogger(level=_level, use_logfile=False)

    def test_post_single(self):
        _now = datetime.now()
        _url = f"{self.base_url}/log.php"
        _params = {
            "app_id": "TESTAPP",
            "log_version": 1,
            "app_version": "1.2.3",
            "session_id": f"{_now.year % 100:02}{_now.month:02}{_now.day:02}{_now.hour:02}{_now.minute:02}{_now.second:02}12345",
            "app_branch": "main",
            "user_id": "TestBed"
        }
        _json = {
            "data": [
                {
                    "event_name"           : "test_event",
                    "event_sequence_index" : 1,
                    "client_time"          : datetime.now().isoformat(),
                    "client_offset"        : "-06:00:00",
                    "event_data"           : {
                        "text_string" : "This is a test event.",
                        "node_id"     : "test.event"
                    },
                    "game_state" : {
                        "level" : 1
                    },
                    "user_data" : {
                        "account_rank" : "beginner"
                    }
                }
            ]
        }
        try:
            response = requests.post(url=_url, headers=self.headers, params=_params, json=_json, timeout=10)
        except Exception as err:
            self.fail(str(err))
        else:
            self.assertNotEqual(response, None)
            self.assertEqual(response.status_code, 200)
            _expected_body = "Foo"
            self.assertEqual(response.text, _expected_body)

    def test_post_multiple(self):
        _now = datetime.now()
        _url = f"{self.base_url}/log.php"
        _params = {
            "app_id": "TESTAPP",
            "log_version": 1,
            "app_version": "1.2.3",
            "session_id": f"{_now.year % 100:02}{_now.month:02}{_now.day:02}{_now.hour:02}{_now.minute:02}{_now.second:02}12345",
            "app_branch": "main",
            "user_id": "TestBed"
        }
        _json = {
            "data": [
                {
                    "event_name"           : "test_event",
                    "event_sequence_index" : 1,
                    "client_time"          : datetime.now().isoformat(),
                    "client_offset"        : "-06:00:00",
                    "event_data"           : {
                        "text_string" : "This is a test event.",
                        "node_id"     : "test.event"
                    },
                    "game_state" : {
                        "level" : 1
                    },
                    "user_data" : {
                        "account_rank" : "beginner"
                    }
                },
                {
                    "event_name"           : "other_test_event",
                    "event_sequence_index" : 2,
                    "client_time"          : datetime.now().isoformat(),
                    "client_offset"        : "-06:00:00",
                    "event_data"           : {
                        "text_string" : "This is another test event.",
                        "node_id"     : "test.event.2"
                    },
                    "game_state" : {
                        "level" : 1
                    },
                    "user_data" : {
                        "account_rank" : "beginner"
                    }
                }
            ]
        }
        try:
            response = requests.post(url=_url, headers=self.headers, params=_params, json=_json, timeout=0.1)
        except Exception as err:
            self.fail(str(err))
        else:
            self.assertNotEqual(response, None)
            self.assertEqual(response.status_code, 200)
            _expected_body = "Foo"
            self.assertEqual(response.text, _expected_body)
