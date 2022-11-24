import { Card, Grid, Paper } from '@mui/material'
import React, { Component } from 'react'
import { connect } from 'react-redux'

import { translate } from '../../traslation/translate'
import Header from '../headers/Header'

export class WelcomePage extends Component {
    render() {
        return (
            <Grid container spacing={1} sx={{ minHeight: '100vh', }} alignContent='flex-start' >
                <Grid item xs={12} >
                    <Header title={translate('general/diamond/single/ucFirstLetterFirstWord')} />
                </Grid>
                <Grid item xs={12} style={{ minHeight: '70vh' }} >
                    <Paper sx={{ m: 1, p: 1 }} >
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Blanditiis modi, aliquam earum provident quas recusandae nisi laborum repellendus! Consectetur quia ratione doloribus officiis sapiente amet neque quod. Quas dolore nulla, voluptatem ad, expedita recusandae repellendus facilis iusto harum perferendis quo iste possimus! Expedita, repellendus alias nam vel tempora autem libero! Vitae, explicabo sit. Quia quae aspernatur rem debitis eius! Quia, molestiae ipsam! Ipsa soluta voluptates laborum at, suscipit numquam omnis cumque iste unde dolorem explicabo voluptas dicta sunt illum officia blanditiis tempora illo temporibus alias totam quis nostrum corrupti? Id, neque obcaecati sunt ex harum fugiat culpa nobis ducimus animi rem dolore optio nihil fugit consequatur ullam dignissimos pariatur officiis eius enim qui dicta hic voluptatem consequuntur. Quaerat asperiores, quisquam alias possimus labore officiis voluptatum eligendi sequi quia eaque maiores fugiat quos nihil laudantium perspiciatis, non tenetur dolorum voluptatem nemo est, voluptates atque? Quibusdam beatae quia maiores hic, ex nemo quis quidem perferendis accusamus provident repellat recusandae quas reiciendis, tempora tempore consequatur tenetur. Facilis, laboriosam. Molestiae illum esse assumenda itaque eum ipsa sequi rerum, alias est reiciendis sed ipsam commodi ad omnis at cumque explicabo? Atque aspernatur quam iste, nostrum esse animi similique? Quae possimus dolorem dicta ipsam aliquid quis cupiditate ad debitis, similique provident quo libero, molestiae, ea veritatis fugiat! Distinctio nihil inventore neque pariatur, laudantium dolorum doloribus totam animi consequuntur provident quod fugiat minima quis modi fuga, iusto et repellat in debitis nobis asperiores autem optio. Saepe id ipsam dolore odio! Veritatis dolorum officiis, vitae, perspiciatis nobis molestias laboriosam rem quaerat error corporis quos illo. Suscipit laboriosam officiis sequi, ipsam, nisi porro in aliquam modi alias repellat explicabo labore ab impedit quia at nobis soluta eos accusamus similique nam? Inventore magni voluptates debitis eligendi assumenda neque distinctio quo autem voluptatibus. Odio, rerum perspiciatis deleniti obcaecati, voluptas qui nisi ut repellendus doloremque deserunt dignissimos fuga praesentium, ullam tempore aspernatur iusto atque. Quas quam a facilis, ratione eius ipsa. Inventore harum aut nobis impedit voluptate in illum eum maxime eius est sint iure deleniti earum explicabo, quis ipsam nulla modi officiis quidem! Voluptatem eveniet perspiciatis doloribus quae reiciendis consectetur quod! Voluptas, laboriosam quas sint quis molestias assumenda nam vel dolor deserunt dolores culpa libero repudiandae magni earum quaerat, qui necessitatibus deleniti maxime recusandae veritatis ipsam inventore quod? Quo, itaque maiores esse reiciendis ea voluptatum at, mollitia quis quasi hic placeat suscipit beatae omnis soluta accusantium illum fugit perferendis minima voluptates ipsa aperiam, est nemo earum porro. At necessitatibus sed ad! Dicta tempora blanditiis illum ratione harum corporis unde! Dicta illo corrupti, porro distinctio eum sequi, autem, eligendi atque alias accusamus ratione ipsum numquam id fugiat hic temporibus labore ea nobis et dignissimos. Quam, nemo amet. Quaerat aspernatur dolorum magnam unde, illo, minus doloribus dolorem accusantium eligendi natus tempore nisi asperiores ipsum! Cum libero soluta deserunt atque explicabo iure, praesentium animi possimus ducimus delectus? Assumenda totam, necessitatibus dolor excepturi delectus eius similique quasi ea tenetur veritatis molestiae architecto omnis deserunt aspernatur recusandae quisquam voluptates beatae labore. Dolorem quidem a repellendus voluptatibus.
                    </Paper>
                    <Card sx={{ m: 1, p: 1 }} >Card</Card>
                </Grid>
                <Grid item xs={12} sx={{ mb: 0 }}>
                    {/* <Footer /> */}
                </Grid>
            </Grid>
        )
    }
}

const mapStateToProps = state => ({
    redux: state
});

export default connect(mapStateToProps)(WelcomePage)
