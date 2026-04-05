# Transaction Status Matrix

| Status | Makna | Boleh Diubah Oleh | Efek Keuangan |
| --- | --- | --- | --- |
| draft | baru dibuat | kasir/admin | belum posting |
| diproses | sedang dikerjakan | operator | belum posting |
| pending_validasi | menunggu kepastian | operator/supervisor | belum posting |
| berhasil | sukses manual | operator/supervisor | posting final |
| gagal | gagal manual | operator/supervisor | tidak posting |
| dibatalkan | dibatalkan | admin/supervisor | tidak posting |
| refund_manual | uang kembali manual | supervisor/admin | catat koreksi |
